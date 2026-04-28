<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Karyawan;
use App\Models\PeriodeGaji;
use App\Models\SlipGaji;
use App\Models\Absensi;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PenggajianController extends Controller
{
    // ─────────────────────────────────────────────────────────────────
    // INDEX - Daftar riwayat periode penggajian
    // ─────────────────────────────────────────────────────────────────
    public function index()
    {
        $periode = PeriodeGaji::withCount('slipGaji')
                              ->orderBy('tanggal_mulai', 'desc')
                              ->paginate(12);

        return view('admin.penggajian.index', compact('periode'));
    }

    // ─────────────────────────────────────────────────────────────────
    // CREATE - Form proses penggajian baru
    // ─────────────────────────────────────────────────────────────────
    public function create()
    {
        $bulanIni  = now()->month;
        $tahunIni  = now()->year;

        // Cek apakah bulan ini sudah ada periode
        $sudahAda = PeriodeGaji::whereMonth('tanggal_mulai', $bulanIni)
                               ->whereYear('tanggal_mulai', $tahunIni)
                               ->exists();

        // Karyawan aktif yang punya gaji pokok
        $karyawan = Karyawan::with(['komponenGaji', 'user'])
                            ->where('is_active', true)
                            ->whereHas('komponenGaji', fn($q) => $q->where('gaji_pokok', '>', 0))
                            ->orderBy('jenis_karyawan')
                            ->orderBy('nama')
                            ->get();

        // Karyawan yang belum punya komponen gaji / gaji pokok 0
        $karyawanBelumAda = Karyawan::where('is_active', true)
                                    ->where(function ($q) {
                                        $q->whereDoesntHave('komponenGaji')
                                          ->orWhereHas('komponenGaji',
                                              fn($r) => $r->where('gaji_pokok', 0));
                                    })
                                    ->count();

        return view('admin.penggajian.create', compact(
            'bulanIni', 'tahunIni', 'sudahAda', 'karyawan', 'karyawanBelumAda'
        ));
    }

    // ─────────────────────────────────────────────────────────────────
    // PROSES - Hitung dan simpan gaji semua karyawan
    // ─────────────────────────────────────────────────────────────────
    public function proses(Request $request)
    {
        $request->validate([
            'bulan' => 'required|integer|between:1,12',
            'tahun' => 'required|integer|min:2020|max:2099',
        ]);

        $bulan = (int) $request->bulan;
        $tahun = (int) $request->tahun;

        // Tanggal mulai & selesai periode
        $tanggalMulai   = Carbon::create($tahun, $bulan, 1)->startOfMonth();
        $tanggalSelesai = $tanggalMulai->copy()->endOfMonth();
        $namaPeriode    = $tanggalMulai->translatedFormat('F Y'); // "Juli 2025"

        // Cegah proses ganda untuk bulan yang sama
        $sudahAda = PeriodeGaji::whereMonth('tanggal_mulai', $bulan)
                               ->whereYear('tanggal_mulai', $tahun)
                               ->exists();

        if ($sudahAda) {
            return back()->with('error',
                "Penggajian {$namaPeriode} sudah pernah diproses."
            );
        }

        DB::beginTransaction();

        try {
            // 1. Buat periode gaji (status: proses)
            $periode = PeriodeGaji::create([
                'nama_periode'    => $namaPeriode,
                'tanggal_mulai'   => $tanggalMulai->toDateString(),
                'tanggal_selesai' => $tanggalSelesai->toDateString(),
                'status'          => 'proses',
            ]);

            // 2. Ambil semua karyawan aktif yang punya gaji pokok
            $karyawanList = Karyawan::with('komponenGaji')
                                    ->where('is_active', true)
                                    ->whereHas('komponenGaji',
                                        fn($q) => $q->where('gaji_pokok', '>', 0))
                                    ->get();

            // 3. Hitung hari kerja di bulan tersebut (Senin–Jumat)
            $jumlahHariKerja = $this->hitungHariKerja($bulan, $tahun);

            // 4. Proses slip per karyawan
            foreach ($karyawanList as $karyawan) {
                $this->buatSlipKaryawan(
                    $karyawan, $periode, $bulan, $tahun, $jumlahHariKerja
                );
            }

            // 5. Update status periode ke final & simpan waktu finalisasi
            $periode->update([
                'status'        => 'final',
                'finalisasi_at' => now(),
                'finalisasi_by' => Auth::id(),
            ]);

            DB::commit();

            return redirect()
                ->route('admin.penggajian.show', $periode->id)
                ->with('success',
                    "Penggajian {$namaPeriode} berhasil diproses. " .
                    "{$karyawanList->count()} slip gaji dibuat."
                );

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memproses penggajian: ' . $e->getMessage());
        }
    }

    // ─────────────────────────────────────────────────────────────────
    // SHOW - Detail periode + daftar semua slip gaji
    // ─────────────────────────────────────────────────────────────────
    public function show(PeriodeGaji $periodeGaji)
    {
        $slipGaji = SlipGaji::with('karyawan.user')
                            ->where('periode_id', $periodeGaji->id)
                            ->orderBy('gaji_bersih', 'desc')
                            ->paginate(20);

        $ringkasan = [
            'total_karyawan'   => SlipGaji::where('periode_id', $periodeGaji->id)->count(),
            'total_pendapatan' => SlipGaji::where('periode_id', $periodeGaji->id)
                                          ->selectRaw('SUM(gaji_pokok + uang_makan + uang_transport) as total')
                                          ->value('total') ?? 0,
            'total_potongan'   => SlipGaji::where('periode_id', $periodeGaji->id)->sum('total_potongan'),
            'total_gaji_bersih'=> SlipGaji::where('periode_id', $periodeGaji->id)->sum('gaji_bersih'),
        ];

        return view('admin.penggajian.show', compact('periodeGaji', 'slipGaji', 'ringkasan'));
    }

    // ─────────────────────────────────────────────────────────────────
    // DETAIL SLIP - Lihat satu slip gaji (admin view)
    // ─────────────────────────────────────────────────────────────────
    public function detailSlip(SlipGaji $slipGaji)
    {
        $slipGaji->load(['karyawan.user', 'karyawan.komponenGaji', 'periodeGaji']);
        return view('admin.penggajian.slip', compact('slipGaji'));
    }

    // ─────────────────────────────────────────────────────────────────
    // PRIVATE: Buat slip gaji satu karyawan
    // ─────────────────────────────────────────────────────────────────
    private function buatSlipKaryawan(
        Karyawan $karyawan,
        PeriodeGaji $periode,
        int $bulan,
        int $tahun,
        int $jumlahHariKerja
    ): void {
        $kg = $karyawan->komponenGaji;

        // ── Rekap absensi bulan tersebut ─────────────────────────────
        $absensi = Absensi::where('karyawan_id', $karyawan->id)
                          ->whereMonth('tanggal', $bulan)
                          ->whereYear('tanggal', $tahun)
                          ->get();

        $totalHadir = $absensi->whereIn('status', ['hadir'])->count();
        $totalTelat = $absensi->where('is_telat', true)->count();
        $totalIzin  = $absensi->whereIn('status', ['izin', 'sakit'])->count();
        $totalCuti  = $absensi->where('status', 'cuti')->count();

        // Hari tidak tercatat sama sekali = alfa
        $totalAlfa  = max(0, $jumlahHariKerja - $absensi->count());

        // ── Hitung pendapatan ─────────────────────────────────────────

        $gajiPokok     = (float) $kg->gaji_pokok;
        $uangMakan     = 0.0;
        $uangTransport = 0.0;

        if (!$karyawan->uang_makan_by_mitra) {
            // Dibayar untuk hari hadir + izin + sakit
            // Cuti: uang makan dipotong (tidak dibayar)
            $hariDibayar   = $totalHadir + $totalIzin;
            $uangMakan     = (float) ($kg->uang_makan     ?? 0) * $hariDibayar;
            $uangTransport = (float) ($kg->uang_transport ?? 0) * $hariDibayar;
        }

        // ── Hitung potongan ───────────────────────────────────────────

        // BPJS dihitung dari gaji pokok
        $potonganBpjsKes = $gajiPokok * ((float) $kg->persen_bpjs_kes / 100);
        $potonganBpjsTk  = $gajiPokok * ((float) $kg->persen_bpjs_tk  / 100);

        // Potongan telat: uang makan + transport per hari (karyawan tetap saja)
        $potonganTelat = 0.0;
        if ($karyawan->isTetap() && !$karyawan->uang_makan_by_mitra) {
            $potPerHari   = config('cbn.uang_makan_harian', 35000)
                          + config('cbn.uang_transport_harian', 45000);
            $potonganTelat = $totalTelat * $potPerHari;
        }

        // Potongan izin (cuti): uang makan 35.000/hari
        $potonganIzin = 0.0;
        if (!$karyawan->uang_makan_by_mitra) {
            $potonganIzin = $totalCuti * config('cbn.potongan_cuti_per_hari', 35000);
        }

        // Potongan alfa: uang makan + transport per hari
        $potonganAlfa = 0.0;
        if (!$karyawan->uang_makan_by_mitra) {
            $potPerHari  = config('cbn.uang_makan_harian', 35000)
                         + config('cbn.uang_transport_harian', 45000);
            $potonganAlfa = $totalAlfa * $potPerHari;
        }

        // Total potongan (gabung alfa ke potongan_izin sesuai kolom tabel)
        $totalPotongan = $potonganBpjsKes + $potonganBpjsTk
                       + $potonganTelat + $potonganIzin + $potonganAlfa;

        $gajiBersih = max(0.0,
            ($gajiPokok + $uangMakan + $uangTransport) - $totalPotongan
        );

        // ── Simpan slip gaji ──────────────────────────────────────────
        SlipGaji::create([
            'karyawan_id'       => $karyawan->id,
            'periode_id'        => $periode->id,
            'gaji_pokok'        => $gajiPokok,
            'uang_makan'        => $uangMakan,
            'uang_transport'    => $uangTransport,
            'total_hadir'       => $totalHadir,
            'total_telat'       => $totalTelat,
            'total_alfa'        => $totalAlfa,
            'total_izin'        => $totalIzin,
            'total_cuti'        => $totalCuti,
            'potongan_telat'    => $potonganTelat,
            'potongan_izin'     => $potonganIzin + $potonganAlfa, // gabung ke satu kolom
            'potongan_bpjs_kes' => $potonganBpjsKes,
            'potongan_bpjs_tk'  => $potonganBpjsTk,
            'total_potongan'    => $totalPotongan,
            'gaji_bersih'       => $gajiBersih,
            'status'            => 'diterbitkan',
            'diterbitkan_at'    => now(),
        ]);
    }

    // ── Hitung hari kerja Senin-Jumat dalam satu bulan ───────────────
    private function hitungHariKerja(int $bulan, int $tahun): int
    {
        $current = Carbon::create($tahun, $bulan, 1);
        $akhir   = $current->copy()->endOfMonth();
        $jumlah  = 0;

        while ($current->lte($akhir)) {
            if ($current->isWeekday()) $jumlah++;
            $current->addDay();
        }

        return $jumlah;
    }
}