<?php

namespace App\Http\Controllers\Karyawan;

use App\Http\Controllers\Controller;
use App\Http\Requests\Karyawan\StorePerizinanRequest;
use App\Http\Requests\Karyawan\StoreDinasLuarRequest;
use App\Models\DetailPerizinan;
use App\Models\JenisPerizinan;
use App\Models\KuotaPerizinan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PerizinanController extends Controller
{
    // ─────────────────────────────────────────────────────────────────
    // INDEX - Daftar pengajuan izin milik karyawan login
    // ─────────────────────────────────────────────────────────────────
    public function index()
    {
        $karyawan = Auth::user();

        $perizinan = $karyawan->perizinan()
                              ->whereHas('jenisPerizinan', fn($q) => $q->where('slug', '!=', 'dinas_luar'))
                              ->with('jenisPerizinan')
                              ->orderBy('created_at', 'desc')
                              ->paginate(10);

        $globalKuota = (int) \App\Models\Configuration::getValue('kuota_cuti_tahunan', 12);

        $kuotaPerizinan = KuotaPerizinan::where('user_id', $karyawan->id)
                              ->where('tahun', now()->year)
                              ->first();

        // Jika data kuota belum ada, buat baru
        if (!$kuotaPerizinan) {
            $kuotaPerizinan = KuotaPerizinan::create([
                'user_id'     => $karyawan->id,
                'tahun'       => now()->year,
                'kuota_total' => $globalKuota,
                'terpakai'    => 0,
                'sisa'        => $globalKuota,
            ]);
        } 
        
        // Auto-sync kuota perizinan berdasarkan pengajuan cuti resmi yang disetujui
        $kuotaPerizinan->syncWithApprovedLeaves();
        // Ambil rincian pengajuan cuti resmi yang disetujui & memotong kuota
        $approvedLeaves = DetailPerizinan::where('user_id', $karyawan->id)
            ->where('status_approval', 'disetujui')
            ->whereHas('jenisPerizinan', fn($q) => $q->where('memotong_kuota', true))
            ->whereYear('tanggal_mulai', now()->year)
            ->orderBy('tanggal_mulai', 'desc')
            ->get();

        // Ambil rincian absensi Alfa (tanpa keterangan) yang memotong kuota
        $alfaRecords = \App\Models\Absensi::where('user_id', $karyawan->id)
            ->where('status', 'alfa')
            ->whereYear('tanggal', now()->year)
            ->orderBy('tanggal', 'desc')
            ->get();

        return view('karyawan.perizinan.index', compact('perizinan', 'kuotaPerizinan', 'karyawan', 'approvedLeaves', 'alfaRecords'));
    }

    // ─────────────────────────────────────────────────────────────────
    // CREATE - Form ajukan perizinan
    // ─────────────────────────────────────────────────────────────────
    public function create()
    {
        $karyawan  = Auth::user();
        $globalKuota = (int) \App\Models\Configuration::getValue('kuota_cuti_tahunan', 12);
        $kuotaPerizinan = KuotaPerizinan::where('user_id', $karyawan->id)
                              ->where('tahun', now()->year)
                              ->first();

        if (!$kuotaPerizinan) {
            $kuotaPerizinan = KuotaPerizinan::create([
                'user_id'     => $karyawan->id,
                'tahun'       => now()->year,
                'kuota_total' => $globalKuota,
                'terpakai'    => 0,
                'sisa'        => $globalKuota,
            ]);
        }

        if ($kuotaPerizinan->kuota_total != $globalKuota) {
            $kuotaPerizinan->update([
                'kuota_total' => $globalKuota,
            ]);
        }

        $kuotaPerizinan->syncWithApprovedLeaves();

        $jenisPerizinan = JenisPerizinan::where('slug', '!=', 'dinas_luar')->get();

        // Cari rekan kerja yang aktif untuk delegasi/backup (hanya karyawan tetap)
        $rekanKerjaList = \App\Models\User::where('id', '!=', $karyawan->id)
            ->whereHas('role', function($q) {
                $q->where('slug', 'karyawan_tetap');
            })
            ->where('is_active', true)
            ->orderBy('nama', 'asc')
            ->get();

        return view('karyawan.perizinan.create', compact('karyawan', 'kuotaPerizinan', 'jenisPerizinan', 'rekanKerjaList'));
    }

    // ─────────────────────────────────────────────────────────────────
    // STORE - Simpan pengajuan perizinan
    // ─────────────────────────────────────────────────────────────────
    public function store(StorePerizinanRequest $request)
    {
        $karyawan = Auth::user();
        $data     = $request->validated();

        $jenis = JenisPerizinan::findOrFail($data['jenis_perizinan_id']);

        // Hitung jumlah hari (exclude weekend? sesuai kebijakan CBN = kalender biasa)
        $mulai    = Carbon::parse($data['tanggal_mulai']);
        $selesai  = Carbon::parse($data['tanggal_selesai']);
        $jumlahHari = (int) $mulai->diffInDays($selesai) + 1;

        // Ambil atau buat kuota perizinan tahun ini
        $kuota = KuotaPerizinan::where('user_id', $karyawan->id)
                               ->where('tahun', $mulai->year)
                               ->first();

        $globalKuota = (int) \App\Models\Configuration::getValue('kuota_cuti_tahunan', 12);

        if (!$kuota) {
            $kuota = KuotaPerizinan::create([
                'user_id'     => $karyawan->id,
                'tahun'       => $mulai->year,
                'kuota_total' => $globalKuota,
                'terpakai'    => 0,
                'sisa'        => $globalKuota,
            ]);
        } else if ($kuota->kuota_total != $globalKuota) {
            // Sinkronkan jatah jika ada perubahan di admin
            $kuota->update([
                'kuota_total' => $globalKuota,
                'sisa'        => $globalKuota - $kuota->terpakai
            ]);
        }

        // Validasi sisa kuota jika jenis yang diajukan memotong kuota
        if ($jenis->memotong_kuota) {
            if ($kuota->sisa < $jumlahHari) {
                return back()->withInput()
                    ->with('error', "Sisa kuota perizinan tidak mencukupi. Sisa: " .
                        $kuota->sisa . " hari, Dibutuhkan: {$jumlahHari} hari.");
            }
        }

        // Upload file bukti
        $filePath = null;
        if ($request->hasFile('file_bukti')) {
            $filePath = $request->file('file_bukti')
                                ->store('perizinan', 'public');
        }

        // Set status flow: jika karyawan tetap mengajukan cuti, butuh approval rekan kerja dulu
        $isCutiKaryawanTetap = $karyawan->isKaryawanTetap() && $jenis->slug === 'cuti';
        $isCutiKaryawanKontrak = $karyawan->isKaryawanKontrak() && $jenis->slug === 'cuti';

        if ($isCutiKaryawanTetap) {
            $statusApproval = 'menunggu_rekan';
        } elseif ($isCutiKaryawanKontrak) {
            $statusApproval = 'menunggu_form_mitra';
        } else {
            $statusApproval = 'menunggu';
        }

        $statusRekan = $isCutiKaryawanTetap ? 'menunggu' : null;
        $rekanKerjaId = $isCutiKaryawanTetap ? ($data['rekan_kerja_id'] ?? null) : null;

        $perizinan = DetailPerizinan::create([
            'user_id'            => $karyawan->id,
            'kuota_perizinan_id' => $kuota->id,
            'jenis_perizinan_id' => $jenis->id,
            'rekan_kerja_id'     => $rekanKerjaId,
            'tanggal_mulai'      => $data['tanggal_mulai'],
            'tanggal_selesai'    => $data['tanggal_selesai'],
            'jumlah_hari'        => $jumlahHari,
            'keterangan'         => $data['keterangan'] ?? null,
            'file_bukti'         => $filePath,
            'status_approval'    => $statusApproval,
            'status_rekan'       => $statusRekan,
        ]);

        if ($isCutiKaryawanTetap) {
            // Kirim notifikasi ke rekan kerja
            \App\Models\Notification::send(
                $rekanKerjaId,
                'Permintaan Backup Cuti 📋',
                "Rekan kerja Anda, {$karyawan->nama}, menunjuk Anda sebagai backup cutinya untuk tanggal {$mulai->format('d M Y')} s/d {$selesai->format('d M Y')}.",
                'info',
                route('karyawan.perizinan.backup.index')
            );

            return redirect()
                ->route('karyawan.perizinan.index')
                ->with('success', 'Pengajuan perizinan berhasil dikirim. Menunggu persetujuan rekan kerja pengganti.');
        }

        if ($statusApproval === 'menunggu') {
            $pimpinans = \App\Models\User::whereHas('role', fn($q) => $q->where('slug', 'pimpinan'))->get();
            foreach ($pimpinans as $pimpinan) {
                \App\Models\Notification::send(
                    $pimpinan->id,
                    'Persetujuan Izin/Cuti Baru 📋',
                    "Karyawan {$karyawan->nama} mengajukan {$jenis->nama_jenis} mulai tanggal {$mulai->format('d/m/Y')} s/d {$selesai->format('d/m/Y')}.",
                    'warning',
                    route('pimpinan.approval.index')
                );
            }
        }

        if ($isCutiKaryawanKontrak) {
            return redirect()
                ->route('karyawan.perizinan.show', $perizinan->id)
                ->with('success', 'Pengajuan perizinan berhasil dibuat. Silakan cetak form, minta tanda tangan pimpinan mitra offline, lalu unggah kembali hasil scan-nya.');
        }

        return redirect()
            ->route('karyawan.perizinan.index')
            ->with('success', 'Pengajuan perizinan berhasil dikirim. Menunggu persetujuan pimpinan.');
    }

    // ─────────────────────────────────────────────────────────────────
    // SHOW - Detail pengajuan
    // ─────────────────────────────────────────────────────────────────
    public function show(DetailPerizinan $perizinan)
    {
        $karyawan = Auth::user();

        // Pastikan hanya milik karyawan yang login
        if ($perizinan->user_id !== $karyawan->id) {
            abort(403, 'Anda tidak memiliki akses ke pengajuan ini.');
        }

        $perizinan->load(['karyawan', 'jenisPerizinan', 'rekanKerja', 'approver']);

        return view('karyawan.perizinan.show', compact('perizinan'));
    }

    // ─────────────────────────────────────────────────────────────────
    // DESTROY - Batalkan pengajuan (hanya yang masih menunggu)
    // ─────────────────────────────────────────────────────────────────
    public function destroy(DetailPerizinan $perizinan)
    {
        $karyawan = Auth::user();

        if ($perizinan->user_id !== $karyawan->id) {
            abort(403, 'Anda tidak memiliki akses ke pengajuan ini.');
        }

        if (!in_array($perizinan->status_approval, ['menunggu', 'menunggu_rekan', 'menunggu_form_mitra'])) {
            return back()->with('error', 'Hanya pengajuan yang belum diproses pimpinan yang bisa dibatalkan.');
        }

        // Hapus file jika ada
        if ($perizinan->file_bukti) {
            Storage::disk('public')->delete($perizinan->file_bukti);
        }

        $perizinan->delete();

        return redirect()
            ->route('karyawan.perizinan.index')
            ->with('success', 'Pengajuan berhasil dibatalkan.');
    }

    // ─────────────────────────────────────────────────────────────────
    // BACKUP INDEX - Daftar delegasi cuti rekan kerja
    // ─────────────────────────────────────────────────────────────────
    public function backupIndex()
    {
        $karyawan = Auth::user();

        $backupRequests = DetailPerizinan::with(['karyawan', 'jenisPerizinan'])
            ->where('rekan_kerja_id', $karyawan->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('karyawan.perizinan.backup', compact('backupRequests'));
    }

    // ─────────────────────────────────────────────────────────────────
    // BACKUP APPROVE - Setujui backup cuti rekan
    // ─────────────────────────────────────────────────────────────────
    public function backupApprove(DetailPerizinan $perizinan)
    {
        $karyawan = Auth::user();

        if ($perizinan->rekan_kerja_id !== $karyawan->id) {
            abort(403, 'Anda tidak memiliki akses ke pengajuan ini.');
        }

        if ($perizinan->status_rekan !== 'menunggu') {
            return back()->with('error', 'Pengajuan ini sudah diproses sebelumnya.');
        }

        $perizinan->update([
            'status_rekan'      => 'disetujui',
            'rekan_approved_at' => now(),
            'status_approval'   => 'menunggu', // Pindah ke antrean pimpinan
        ]);

        // Notifikasi ke pemohon
        \App\Models\Notification::send(
            $perizinan->user_id,
            'Backup Cuti Disetujui 🤝',
            "Rekan kerja Anda, {$karyawan->nama}, bersedia mem-backup pekerjaan Anda. Pengajuan cuti kini diteruskan ke Pimpinan.",
            'success',
            route('karyawan.perizinan.show', $perizinan->id)
        );

        // Notifikasi ke Pimpinan
        $pimpinans = \App\Models\User::whereHas('role', fn($q) => $q->where('slug', 'pimpinan'))->get();
        foreach ($pimpinans as $pimpinan) {
            \App\Models\Notification::send(
                $pimpinan->id,
                'Persetujuan Cuti Baru (Backup OK) 📋',
                "Pengajuan cuti Karyawan Tetap {$perizinan->karyawan->nama} telah disetujui oleh rekan kerja pengganti ({$karyawan->nama}) dan memerlukan persetujuan Anda.",
                'warning',
                route('pimpinan.approval.index')
            );
        }

        return redirect()
            ->route('karyawan.perizinan.backup.index')
            ->with('success', 'Berhasil menyetujui backup pekerjaan rekan kerja.');
    }

    // ─────────────────────────────────────────────────────────────────
    // BACKUP REJECT - Tolak backup cuti rekan
    // ─────────────────────────────────────────────────────────────────
    public function backupReject(DetailPerizinan $perizinan)
    {
        $karyawan = Auth::user();

        if ($perizinan->rekan_kerja_id !== $karyawan->id) {
            abort(403, 'Anda tidak memiliki akses ke pengajuan ini.');
        }

        if ($perizinan->status_rekan !== 'menunggu') {
            return back()->with('error', 'Pengajuan ini sudah diproses sebelumnya.');
        }

        $perizinan->update([
            'status_rekan'    => 'ditolak',
            'status_approval' => 'ditolak',
            'alasan_tolak'    => "Ditolak oleh rekan kerja pengganti ({$karyawan->nama}).",
        ]);

        // Notifikasi ke pemohon
        \App\Models\Notification::send(
            $perizinan->user_id,
            'Backup Cuti Ditolak ❌',
            "Rekan kerja Anda, {$karyawan->nama}, menolak mem-backup pekerjaan Anda. Pengajuan Anda otomatis ditolak.",
            'danger',
            route('karyawan.perizinan.show', $perizinan->id)
        );

        return redirect()
            ->route('karyawan.perizinan.backup.index')
            ->with('success', 'Berhasil menolak backup pekerjaan rekan kerja.');
    }

    // ─────────────────────────────────────────────────────────────────
    // PRINT - Cetak surat permohonan cuti
    // ─────────────────────────────────────────────────────────────────
    public function print(DetailPerizinan $perizinan)
    {
        $karyawan = Auth::user();

        // Pastikan hanya milik karyawan yang login
        if ($perizinan->user_id !== $karyawan->id) {
            abort(403, 'Anda tidak memiliki akses ke pengajuan ini.');
        }

        $perizinan->load(['karyawan', 'jenisPerizinan', 'rekanKerja', 'approver']);

        // Mengambil sisa kuota untuk cetakan bagian pertimbangan
        $kuotaPerizinan = KuotaPerizinan::where('user_id', $karyawan->id)
            ->where('tahun', $perizinan->tanggal_mulai->year)
            ->first();

        return view('karyawan.perizinan.print', compact('perizinan', 'kuotaPerizinan'));
    }

    // ─────────────────────────────────────────────────────────────────
    // UPLOAD MITRA - Unggah form cuti bertanda tangan mitra
    // ─────────────────────────────────────────────────────────────────
    public function uploadFormMitra(\Illuminate\Http\Request $request, DetailPerizinan $perizinan)
    {
        $karyawan = Auth::user();

        // Pastikan hanya milik karyawan yang login
        if ($perizinan->user_id !== $karyawan->id) {
            abort(403, 'Anda tidak memiliki akses ke pengajuan ini.');
        }

        if ($perizinan->status_approval !== 'menunggu_form_mitra') {
            return back()->with('error', 'Status pengajuan tidak valid untuk upload.');
        }

        $request->validate([
            'file_bukti' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ], [
            'file_bukti.required' => 'Wajib mengunggah scan/foto Form Cuti yang telah ditandatangani.',
            'file_bukti.mimes' => 'File harus berformat PDF, JPG, atau PNG.',
            'file_bukti.max' => 'Ukuran file maksimal 2MB.',
        ]);

        if ($request->hasFile('file_bukti')) {
            $filePath = $request->file('file_bukti')->store('perizinan', 'public');
            $perizinan->update([
                'file_bukti' => $filePath,
                'status_approval' => 'menunggu', // Kirim ke pimpinan
            ]);

            // Kirim notifikasi ke Pimpinan
            $pimpinans = \App\Models\User::whereHas('role', fn($q) => $q->where('slug', 'pimpinan'))->get();
            foreach ($pimpinans as $pimpinan) {
                \App\Models\Notification::send(
                    $pimpinan->id,
                    'Upload Form Mitra Cuti Kontrak 📋',
                    "Karyawan Kontrak {$karyawan->nama} telah mengunggah Form Mitra untuk pengajuan cutinya. Silakan periksa dan setujui.",
                    'warning',
                    route('pimpinan.approval.index')
                );
            }
        }

        return redirect()
            ->route('karyawan.perizinan.show', $perizinan->id)
            ->with('success', 'Form Cuti berhasil diunggah. Pengajuan kini diteruskan ke Pimpinan CBN.');
    }

    // ─────────────────────────────────────────────────────────────────
    // DINAS LUAR KOTA - Dedicated Module Methods
    // ─────────────────────────────────────────────────────────────────
    public function dinasLuarIndex()
    {
        $karyawan = Auth::user();

        $dinasLuarRequests = $karyawan->perizinan()
            ->whereHas('jenisPerizinan', fn($q) => $q->where('slug', 'dinas_luar'))
            ->with('jenisPerizinan')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('karyawan.dinas-luar.index', compact('dinasLuarRequests', 'karyawan'));
    }

    public function dinasLuarCreate()
    {
        $karyawan = Auth::user();
        $jenisDinas = JenisPerizinan::where('slug', 'dinas_luar')->firstOrFail();

        return view('karyawan.dinas-luar.create', compact('karyawan', 'jenisDinas'));
    }

    public function dinasLuarStore(StoreDinasLuarRequest $request)
    {
        $karyawan = Auth::user();
        $data     = $request->validated();

        $jenisDinas = JenisPerizinan::where('slug', 'dinas_luar')->firstOrFail();

        $mulai      = Carbon::parse($data['tanggal_mulai']);
        $selesai    = Carbon::parse($data['tanggal_selesai']);
        $jumlahHari = (int) $mulai->diffInDays($selesai) + 1;

        $kuota = KuotaPerizinan::firstOrCreate([
            'user_id' => $karyawan->id,
            'tahun'   => $mulai->year,
        ], [
            'kuota_total' => (int) \App\Models\Configuration::getValue('kuota_cuti_tahunan', 12),
            'terpakai'    => 0,
            'sisa'        => (int) \App\Models\Configuration::getValue('kuota_cuti_tahunan', 12),
        ]);

        $filePath = null;
        if ($request->hasFile('file_bukti')) {
            $filePath = $request->file('file_bukti')->store('perizinan', 'public');
        }

        $perizinan = DetailPerizinan::create([
            'user_id'            => $karyawan->id,
            'kuota_perizinan_id' => $kuota->id,
            'jenis_perizinan_id' => $jenisDinas->id,
            'tanggal_mulai'      => $data['tanggal_mulai'],
            'tanggal_selesai'    => $data['tanggal_selesai'],
            'jumlah_hari'        => $jumlahHari,
            'keterangan'         => $data['keterangan'],
            'file_bukti'         => $filePath,
            'status_approval'    => 'menunggu',
        ]);

        $pimpinans = \App\Models\User::whereHas('role', fn($q) => $q->where('slug', 'pimpinan'))->get();
        foreach ($pimpinans as $pimpinan) {
            \App\Models\Notification::send(
                $pimpinan->id,
                'Pengajuan Dinas Luar Kota Baru 📍',
                "Karyawan {$karyawan->nama} mengajukan Dinas Luar Kota mulai {$mulai->format('d/m/Y')} s/d {$selesai->format('d/m/Y')}.",
                'warning',
                route('pimpinan.approval.index')
            );
        }

        return redirect()
            ->route('karyawan.dinas-luar.index')
            ->with('success', 'Pengajuan Dinas Luar Kota berhasil dikirim. Menunggu persetujuan Pimpinan.');
    }

    public function dinasLuarShow(DetailPerizinan $perizinan)
    {
        $karyawan = Auth::user();

        if ($perizinan->user_id !== $karyawan->id) {
            abort(403, 'Anda tidak memiliki akses ke pengajuan ini.');
        }

        $perizinan->load(['karyawan', 'jenisPerizinan', 'approver']);

        return view('karyawan.dinas-luar.show', compact('perizinan'));
    }
}

