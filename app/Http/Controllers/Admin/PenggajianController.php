<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\PeriodeGaji;
use App\Models\SlipGajiPeriode;
use App\Models\Absensi;
use App\Models\Configuration;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PenggajianController extends Controller
{
    public function index()
    {
        $periode = PeriodeGaji::withCount('slipGaji')
                              ->orderBy('tanggal_mulai', 'desc')
                              ->paginate(12);

        return view('admin.penggajian.index', compact('periode'));
    }

    public function create()
    {
        $bulanIni  = now()->month;
        $tahunIni  = now()->year;

        $sudahAda = PeriodeGaji::whereMonth('tanggal_mulai', $bulanIni)
                               ->whereYear('tanggal_mulai', $tahunIni)
                               ->exists();

        // Ambil semua periode yang sudah ada untuk validasi real-time di JS
        $periodeTerdaftar = PeriodeGaji::select('tanggal_mulai')->get()->map(function($p) {
            return [
                'bulan' => (int) $p->tanggal_mulai->format('m'),
                'tahun' => (int) $p->tanggal_mulai->format('Y')
            ];
        })->toArray();

        $karyawan = User::with(['komponenGaji'])
                            ->where('is_active', true)
                            ->whereHas('role', fn($q) => $q->whereIn('slug', ['karyawan_tetap', 'karyawan_kontrak']))
                            ->whereHas('komponenGaji', fn($q) => $q->where('komponen_gaji_id', 'MKG-00001')->where('nominal', '>', 0))
                            ->orderBy('role_id')
                            ->orderBy('nama')
                            ->get();

        $karyawanBelumAda = User::where('is_active', true)
                                    ->whereHas('role', fn($q) => $q->whereIn('slug', ['karyawan_tetap', 'karyawan_kontrak']))
                                    ->where(function ($q) {
                                        $q->whereDoesntHave('komponenGaji', fn($r) => $r->where('komponen_gaji_id', 'MKG-00001'))
                                          ->orWhereHas('komponenGaji',
                                              fn($r) => $r->where('komponen_gaji_id', 'MKG-00001')->where('nominal', 0));
                                    })
                                    ->count();

        return view('admin.penggajian.create', compact(
            'bulanIni', 'tahunIni', 'sudahAda', 'karyawan', 'karyawanBelumAda', 'periodeTerdaftar'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'bulan' => 'required|integer|between:1,12',
            'tahun' => 'required|integer|min:2020|max:2099',
        ]);

        $bulan = (int) $request->bulan;
        $tahun = (int) $request->tahun;

        // Cut-off Tanggal 26 Bulan Lalu s.d 25 Bulan Ini
        $tanggalMulai   = Carbon::create($tahun, $bulan, 26)->subMonth()->startOfDay();
        $tanggalSelesai = Carbon::create($tahun, $bulan, 25)->endOfDay();
        $namaPeriode    = Carbon::create($tahun, $bulan, 1)->translatedFormat('F Y');

        $sudahAda = PeriodeGaji::whereMonth('tanggal_selesai', $bulan)
                               ->whereYear('tanggal_selesai', $tahun)
                               ->exists();

        if ($sudahAda) {
            return back()->with('error', "Periode penggajian {$namaPeriode} sudah pernah dibuat.");
        }

        $periode = PeriodeGaji::create([
            'nama_periode'    => $namaPeriode,
            'tanggal_mulai'   => $tanggalMulai->toDateString(),
            'tanggal_selesai' => $tanggalSelesai->toDateString(),
            'status'          => 'draft',
        ]);

        return redirect()
            ->route('admin.penggajian.show', $periode->id)
            ->with('success', "Periode {$namaPeriode} berhasil dibuat sebagai Draft.");
    }
    public function hitung(PeriodeGaji $periodeGaji)
    {
        if (!$periodeGaji->isDraft()) {
            return back()->with('error', 'Periode penggajian ini sudah pernah diproses/final.');
        }

        // Batasi pemrosesan gaji berdasarkan tanggal minimal dari konfigurasi sistem (default: 25)
        $batasTanggal = (int)\App\Models\Configuration::getValue('batas_tanggal_gaji', 25);
        $periodeBulanTahun = $periodeGaji->tanggal_selesai->format('Y-m');
        $sekarangBulanTahun = now()->format('Y-m');
        if ($periodeBulanTahun >= $sekarangBulanTahun && now()->day < $batasTanggal) {
            return back()->with('error', "Gagal: Proses penggajian untuk periode berjalan baru diperbolehkan mulai tanggal {$batasTanggal}.");
        }

        DB::beginTransaction();
        try {
            $namaPeriode = $periodeGaji->nama_periode;
            $hasSlips = $periodeGaji->slipGaji()->exists();

            if ($hasSlips) {
                // Update rejected or revised slips back to draft (neutral) and clear rejection reasons
                $periodeGaji->slipGaji()
                            ->whereIn('status', ['ditolak', 'direvisi'])
                            ->update([
                                'status' => 'draft',
                                'alasan_tolak' => null,
                            ]);

                // Ensure all other slips in this period are set back to draft status
                $periodeGaji->slipGaji()->where('status', '!=', 'draft')->update(['status' => 'draft']);

                $periodeGaji->update([
                    'status' => 'proses',
                ]);
            } else {
                // First-time generation: clear and compute from scratch
                $periodeGaji->slipGaji()->delete();

                $periodeGaji->update([
                    'status' => 'proses',
                ]);

                $karyawanList = User::with('komponenGaji')
                                        ->where('is_active', true)
                                        ->whereHas('role', fn($q) => $q->whereIn('slug', ['karyawan_tetap', 'karyawan_kontrak']))
                                        ->whereHas('komponenGaji',
                                            fn($q) => $q->where('komponen_gaji_id', 'MKG-00001')->where('nominal', '>', 0))
                                        ->get();

                $jumlahHariKerja = $this->hitungHariKerja($periodeGaji->tanggal_mulai, $periodeGaji->tanggal_selesai);

                foreach ($karyawanList as $karyawan) {
                    $this->buatSlipKaryawan(
                        $karyawan, $periodeGaji, $jumlahHariKerja
                    );
                }
            }

            // Kirim notifikasi ke Pimpinan
            $pimpinans = User::whereHas('role', function($q) {
                $q->where('slug', 'pimpinan');
            })->get();
            foreach ($pimpinans as $pimpinan) {
                \App\Models\Notification::send(
                    $pimpinan->id,
                    'Persetujuan Gaji Diperlukan 💰',
                    "Penggajian periode {$namaPeriode} telah diajukan kembali untuk persetujuan Anda.",
                    'warning',
                    route('pimpinan.monitoring-gaji.index', ['periode_id' => $periodeGaji->id])
                );
            }

            DB::commit();

            return redirect()
                ->route('admin.penggajian.show', $periodeGaji->id)
                ->with('success', "Penggajian periode {$namaPeriode} berhasil diajukan kembali ke Pimpinan.");

        } catch (\Exception $e) {
            DB::rollBack();
            $periodeGaji->update([
                'status' => 'draft',
            ]);
            return back()->with('error', 'Gagal memproses penggajian: ' . $e->getMessage());
        }
    }

    public function destroy(PeriodeGaji $periodeGaji)
    {
        if (!$periodeGaji->isDraft()) {
            return back()->with('error', 'Hanya periode bertipe Draft yang dapat dihapus.');
        }

        $nama = $periodeGaji->nama_periode;
        $periodeGaji->delete();

        return redirect()
            ->route('admin.penggajian.index')
            ->with('success', "Periode {$nama} berhasil dihapus.");
    }

    public function show(PeriodeGaji $periodeGaji)
    {
        if ($periodeGaji->isDraft()) {
            $karyawan = User::with(['komponenGaji'])
                                ->where('is_active', true)
                                ->whereHas('role', fn($q) => $q->whereIn('slug', ['karyawan_tetap', 'karyawan_kontrak']))
                                ->whereHas('komponenGaji', fn($q) => $q->where('komponen_gaji_id', 'MKG-00001')->where('nominal', '>', 0))
                                ->orderBy('role_id')
                                ->orderBy('nama')
                                ->get();

            $karyawanBelumAda = User::where('is_active', true)
                                        ->whereHas('role', fn($q) => $q->whereIn('slug', ['karyawan_tetap', 'karyawan_kontrak']))
                                        ->where(function ($q) {
                                            $q->whereDoesntHave('komponenGaji', fn($r) => $r->where('komponen_gaji_id', 'MKG-00001'))
                                              ->orWhereHas('komponenGaji',
                                                  fn($r) => $r->where('komponen_gaji_id', 'MKG-00001')->where('nominal', 0));
                                        })
                                        ->count();

            $slipDitolak = SlipGajiPeriode::with('karyawan')
                                        ->where('periode_id', $periodeGaji->id)
                                        ->whereIn('status', ['ditolak', 'direvisi'])
                                        ->get();

            return view('admin.penggajian.show', compact('periodeGaji', 'karyawan', 'karyawanBelumAda', 'slipDitolak'));
        }

        $slipGaji = SlipGajiPeriode::with('karyawan')
                            ->where('periode_id', $periodeGaji->id)
                            ->orderBy('gaji_bersih', 'desc')
                            ->paginate(20);

        $ringkasan = [
            'total_karyawan'   => SlipGajiPeriode::where('periode_id', $periodeGaji->id)->count(),
            'total_pendapatan' => \App\Models\DetailGajiKomponen::whereHas('slipGaji', function($q) use ($periodeGaji) {
                                      $q->where('periode_id', $periodeGaji->id);
                                  })->whereHas('komponenGaji', function($q) {
                                      $q->where('tipe', 'pendapatan');
                                  })->sum('nominal'),
            'total_potongan'   => SlipGajiPeriode::where('periode_id', $periodeGaji->id)->sum('total_potongan'),
            'total_gaji_bersih'=> SlipGajiPeriode::where('periode_id', $periodeGaji->id)->sum('gaji_bersih'),
        ];

        return view('admin.penggajian.show', compact('periodeGaji', 'slipGaji', 'ringkasan'));
    }

    public function detailSlip(SlipGajiPeriode $slipGaji)
    {
        $slipGaji->load(['karyawan', 'karyawan.komponenGaji', 'periodeGaji']);
        return view('admin.penggajian.slip', compact('slipGaji'));
    }

    public function officialSlip(SlipGajiPeriode $slipGaji)
    {
        $slipGaji->load(['karyawan', 'karyawan.komponenGaji', 'periodeGaji']);
        
        $karyawan = $slipGaji->karyawan;
        $jabatanUmumList = ['CS', 'CS ATM', 'Ekspedisi'];
        $isKontrakUmum = $karyawan->isKaryawanKontrak() && (
            in_array($karyawan->jabatan, $jabatanUmumList) || $karyawan->divisi === 'umum'
        );

        if (!$isKontrakUmum) {
            \App\Models\DetailGajiKomponen::where('slip_gaji_periode_id', $slipGaji->id)
                ->where('komponen_gaji_id', 'MKG-00002')
                ->delete();
            $slipGaji->unsetRelation('details');
            $slipGaji->load('details');
        }

        if ($karyawan->jabatan === 'Satpam') {
            $extraFooding = (float) Configuration::getValue('extra_fooding_satpam', 100000);
            \App\Models\DetailGajiKomponen::updateOrCreate([
                'slip_gaji_periode_id' => $slipGaji->id,
                'komponen_gaji_id'     => 'MKG-00013',
            ], [
                'nama_komponen' => 'Pendapatan Lainnya',
                'tipe'          => 'pendapatan',
                'nominal'       => $extraFooding > 0 ? $extraFooding : 100000,
            ]);
            $slipGaji->unsetRelation('details');
            $slipGaji->load('details');
        }

        $admUmum = \App\Models\User::where('jabatan', 'Staff Administrasi & Umum')
                                    ->where('nama', '!=', 'Administrator Utama')
                                    ->first();

        return view('admin.penggajian.slip_official', compact('slipGaji', 'admUmum'));
    }

    private function buatSlipKaryawan(
        User $karyawan,
        PeriodeGaji $periode,
        int $jumlahHariKerja
    ): void {
        $kg = $karyawan->komponenGaji;

        $absensi = Absensi::where('user_id', $karyawan->id)
                          ->whereBetween('tanggal', [$periode->tanggal_mulai->toDateString(), $periode->tanggal_selesai->toDateString()])
                          ->get();

        $totalHadir = $absensi->whereIn('status', ['hadir', 'telat'])->count();
        $totalTelat = $absensi->where('is_telat', true)->count();
        $totalIzin  = $absensi->whereIn('status', ['izin', 'sakit'])->count();
        $totalCuti  = $absensi->where('status', 'cuti')->count();
        $totalAlfa  = $absensi->where('status', 'alfa')->count() + max(0, $jumlahHariKerja - $absensi->count());

        $gajiPokok     = (float) $kg->gaji_pokok;
        
        // Uang Makan & Transport khusus Karyawan Tetap (dihitung per kehadiran)
        // Untuk Karyawan Kontrak (HC & Umum), Uang Makan & Transport diset 0.0 (tampil '-' pada slip)
        $isTetap       = $karyawan->isKaryawanTetap();
        $defMakan      = Configuration::getValue('uang_makan_default', 35000);
        $defTransport  = Configuration::getValue('uang_transport_default', 45000);
        
        $uangMakan     = $isTetap ? (float) ($kg->uang_makan     ?? $defMakan) * $totalHadir : 0.0;
        $uangTransport = $isTetap ? (float) ($kg->uang_transport ?? $defTransport) * $totalHadir : 0.0;

        // BPJS dengan Fallback Standar (Kesehatan 1%, TK 2%)
        $persenKes = $kg->persen_bpjs_kes ?? Configuration::getValue('persen_bpjs_kes_default', 1);
        $persenTk  = $kg->persen_bpjs_tk  ?? Configuration::getValue('persen_bpjs_tk_default', 2);

        $potonganBpjsKes = $gajiPokok * ((float) $persenKes / 100);
        $potonganBpjsTk  = $gajiPokok * ((float) $persenTk  / 100);

        // Tidak ada potongan denda lagi untuk telat, izin/cuti, atau alfa
        $potonganTelat = 0.0;
        $potonganIzin = 0.0;
        $potonganAlfa = 0.0;

        // Komponen Official PT CBN - Gaji Pangan khusus Karyawan Kontrak Divisi Umum (CS, CS ATM, Ekspedisi)
        $jabatanUmumList = ['CS', 'CS ATM', 'Ekspedisi'];
        $isKontrakUmum = $karyawan->isKaryawanKontrak() && (
            in_array($karyawan->jabatan, $jabatanUmumList) || $karyawan->divisi === 'umum'
        );
        $gajiPanganDefault = (float) Configuration::getValue('tunjangan_pangan_kontrak_umum', 805000);
        $jumlahHariKerjaEff = max(1, $jumlahHariKerja ?? 23);
        $tarifPanganHarian = $gajiPanganDefault / $jumlahHariKerjaEff;
        $gajiPangan = $isKontrakUmum ? round($tarifPanganHarian * $totalHadir) : 0.0;
        
        // Tunjangan BPJS murni sesuai persentase yang dimasukkan (berlaku sama untuk Tetap & Kontrak)
        $tunjanganBpjsKes = $potonganBpjsKes;
        $tunjanganBpjsTk  = $potonganBpjsTk;

        // Extra Fooding / Uang Saku Tambahan (Pendapatan Lainnya) khusus Satpam Rp 100.000
        $extraFoodingSatpam = (float) Configuration::getValue('extra_fooding_satpam', 100000);
        $pendapatanLainnya  = ($karyawan->jabatan === 'Satpam') ? $extraFoodingSatpam : 0.0;

        $totalPotongan = $potonganBpjsKes + $potonganBpjsTk;

        $totalPendapatan = $gajiPokok + $gajiPangan + $uangMakan + $uangTransport + $tunjanganBpjsKes + $tunjanganBpjsTk + $pendapatanLainnya;
        $gajiBersih = max(0.0, $totalPendapatan - $totalPotongan);

        $slipGaji = SlipGajiPeriode::create([
            'user_id'           => $karyawan->id,
            'periode_id'        => $periode->id,
            'total_hadir'       => $totalHadir,
            'total_telat'       => $totalTelat,
            'total_alfa'        => $totalAlfa,
            'total_izin'        => $totalIzin,
            'total_cuti'        => $totalCuti,
            'total_potongan'    => $totalPotongan,
            'gaji_bersih'       => $gajiBersih,
            'status'            => 'draft',
            'diterbitkan_at'    => null,
        ]);

        // Simpan Detail Komponen Gaji (Many-to-Many)
        $detailKomponen = [
            'MKG-00001' => $gajiPokok,
            'MKG-00002' => $gajiPangan,
            'MKG-00003' => $uangMakan,
            'MKG-00004' => $uangTransport,
            'MKG-00005' => $tunjanganBpjsTk,
            'MKG-00006' => $tunjanganBpjsKes,
            'MKG-00007' => 0.0, // potongan telat ditiadakan
            'MKG-00008' => 0.0, // potongan izin/alfa ditiadakan
            'MKG-00009' => $potonganBpjsKes,
            'MKG-00010' => $potonganBpjsTk,
            'MKG-00013' => $pendapatanLainnya,
        ];

        foreach ($detailKomponen as $mkgId => $nominal) {
            if ($nominal > 0) {
                \App\Models\DetailGajiKomponen::create([
                    'user_id'              => $karyawan->id,
                    'slip_gaji_periode_id' => $slipGaji->id,
                    'komponen_gaji_id'     => $mkgId,
                    'nominal'              => $nominal,
                ]);
            }
        }

        // Link Perizinan yang diajukan di periode ini ke Slip Gaji ini
        \App\Models\DetailPerizinan::whereHas('kuotaPerizinan', fn($q) => $q->where('user_id', $karyawan->id))
                             ->where('status_approval', 'disetujui')
                             ->whereBetween('tanggal_mulai', [$periode->tanggal_mulai->toDateString(), $periode->tanggal_selesai->toDateString()])
                             ->update(['slip_gaji_periode_id' => $slipGaji->id]);
    }

    public function updateAbsensi(Request $request, SlipGajiPeriode $slipGaji)
    {
        $request->validate([
            'total_hadir'    => 'required|integer|min:0',
            'total_telat'    => 'required|integer|min:0',
            'total_alfa'     => 'required|integer|min:0',
            'total_izin'     => 'required|integer|min:0',
            'total_cuti'     => 'required|integer|min:0',
            'gaji_pokok'     => 'required|numeric|min:0',
            'uang_makan'     => 'nullable|numeric|min:0',
            'uang_transport' => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $karyawan = $slipGaji->karyawan;
            $periode = $slipGaji->periode;

            // 1. Update master salary components for this employee
            $karyawan->updateKomponenGaji([
                'gaji_pokok'      => $request->gaji_pokok,
                'uang_makan'      => $karyawan->uang_makan_by_mitra ? null : $request->uang_makan,
                'uang_transport'  => $karyawan->uang_makan_by_mitra ? null : $request->uang_transport,
            ]);

            // Re-load components to get correct values
            $karyawan->load('komponenGaji');
            $kg = $karyawan->komponenGaji;

            // 2. Perform recalculation for the current slip
            $totalHadir = (int) $request->total_hadir;
            $totalTelat = (int) $request->total_telat;
            $totalAlfa  = (int) $request->total_alfa;
            $totalIzin  = (int) $request->total_izin;
            $totalCuti  = (int) $request->total_cuti;

            $gajiPokok     = (float) $request->gaji_pokok;
            $defMakan      = Configuration::getValue('uang_makan_default', 35000);
            $defTransport  = Configuration::getValue('uang_transport_default', 45000);
            
            $isTetap       = $karyawan->isKaryawanTetap();
            $uangMakan     = $isTetap ? (float) ($request->uang_makan     ?? $defMakan) * $totalHadir : 0.0;
            $uangTransport = $isTetap ? (float) ($request->uang_transport ?? $defTransport) * $totalHadir : 0.0;

            $persenKes = $kg->persen_bpjs_kes ?? Configuration::getValue('persen_bpjs_kes_default', 1);
            $persenTk  = $kg->persen_bpjs_tk  ?? Configuration::getValue('persen_bpjs_tk_default', 2);

            $potonganBpjsKes = $gajiPokok * ((float) $persenKes / 100);
            $potonganBpjsTk  = $gajiPokok * ((float) $persenTk  / 100);

            $isKontrakUmum = $karyawan->isKaryawanKontrak() && (
                in_array($karyawan->jabatan, ['CS', 'CS ATM', 'Ekspedisi']) || $karyawan->divisi === 'umum'
            );
            $gajiPanganDefault = (float) Configuration::getValue('tunjangan_pangan_kontrak_umum', 805000);
            $jumlahHariKerjaEff = max(1, (int) ($slipGaji->periodeGaji?->jumlah_hari_kerja ?? 23));
            $tarifPanganHarian = $gajiPanganDefault / $jumlahHariKerjaEff;
            $gajiPangan        = $isKontrakUmum ? round($tarifPanganHarian * $totalHadir) : 0.0;
            $extraFoodingSatpam = (float) Configuration::getValue('extra_fooding_satpam', 100000);
            $pendapatanLainnya  = ($karyawan->jabatan === 'Satpam') ? $extraFoodingSatpam : 0.0;

            $potonganPinjaman = (float) ($request->potongan_pinjaman ?? 0);

            $totalPotongan = $potonganBpjsKes + $potonganBpjsTk + $potonganPinjaman;
            $totalPendapatan = $gajiPokok + $gajiPangan + $uangMakan + $uangTransport + $tunjanganBpjsKes + $tunjanganBpjsTk + $pendapatanLainnya;
            $gajiBersih = max(0.0, $totalPendapatan - $totalPotongan);

            // 3. Update Slip Gaji Periode
            $slipGaji->update([
                'total_hadir'    => $totalHadir,
                'total_telat'    => $totalTelat,
                'total_alfa'     => $totalAlfa,
                'total_izin'     => $totalIzin,
                'total_cuti'     => $totalCuti,
                'total_potongan' => $totalPotongan,
                'gaji_bersih'    => $gajiBersih,
                'status'         => 'direvisi',
            ]);

            // 4. Rebuild Slip Component Details
            $slipGaji->details()->delete();

            $detailKomponen = [
                'MKG-00001' => $gajiPokok,
                'MKG-00002' => $gajiPangan,
                'MKG-00003' => $uangMakan,
                'MKG-00004' => $uangTransport,
                'MKG-00005' => $tunjanganBpjsTk,
                'MKG-00006' => $tunjanganBpjsKes,
                'MKG-00007' => 0.0,
                'MKG-00008' => 0.0,
                'MKG-00009' => $potonganBpjsKes,
                'MKG-00010' => $potonganBpjsTk,
                'MKG-00013' => $pendapatanLainnya,
                'MKG-00014' => $potonganPinjaman,
            ];

            foreach ($detailKomponen as $mkgId => $nominal) {
                if ($nominal > 0) {
                    \App\Models\DetailGajiKomponen::create([
                        'user_id'              => $karyawan->id,
                        'slip_gaji_periode_id' => $slipGaji->id,
                        'komponen_gaji_id'     => $mkgId,
                        'nominal'              => $nominal,
                    ]);
                }
            }

            DB::commit();
            return back()->with('success', "Data absensi & komponen gaji untuk {$karyawan->nama} berhasil direvisi.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memperbarui data revisi: ' . $e->getMessage());
        }
    }

    private function hitungHariKerja(Carbon $dari, Carbon $sampai): int
    {
        $akhir   = $sampai->copy()->endOfDay();

        // Jika akhir periode berada di masa depan, batasi perhitungan sampai hari ini
        if ($akhir->isFuture()) {
            $akhir = now()->endOfDay();
        }

        return \App\Helpers\AttendanceHelper::countWorkingDays($dari, $akhir);
    }
}
