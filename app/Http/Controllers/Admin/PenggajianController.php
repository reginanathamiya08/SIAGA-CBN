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

        $karyawan = Karyawan::with(['komponenGaji', 'user'])
                            ->where('is_active', true)
                            ->whereHas('komponenGaji', fn($q) => $q->where('gaji_pokok', '>', 0))
                            ->orderBy('jenis_karyawan')
                            ->orderBy('nama')
                            ->get();

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

    public function proses(Request $request)
    {
        $request->validate([
            'bulan' => 'required|integer|between:1,12',
            'tahun' => 'required|integer|min:2020|max:2099',
        ]);

        $bulan = (int) $request->bulan;
        $tahun = (int) $request->tahun;

        $tanggalMulai   = Carbon::create($tahun, $bulan, 1)->startOfMonth();
        $tanggalSelesai = $tanggalMulai->copy()->endOfMonth();
        $namaPeriode    = $tanggalMulai->translatedFormat('F Y');

        $sudahAda = PeriodeGaji::whereMonth('tanggal_mulai', $bulan)
                               ->whereYear('tanggal_mulai', $tahun)
                               ->exists();

        if ($sudahAda) {
            return back()->with('error', "Penggajian {$namaPeriode} sudah pernah diproses.");
        }

        DB::beginTransaction();

        try {
            $periode = PeriodeGaji::create([
                'nama_periode'    => $namaPeriode,
                'tanggal_mulai'   => $tanggalMulai->toDateString(),
                'tanggal_selesai' => $tanggalSelesai->toDateString(),
                'status'          => 'proses',
            ]);

            $karyawanList = Karyawan::with('komponenGaji')
                                    ->where('is_active', true)
                                    ->whereHas('komponenGaji',
                                        fn($q) => $q->where('gaji_pokok', '>', 0))
                                    ->get();

            $jumlahHariKerja = $this->hitungHariKerja($bulan, $tahun);

            foreach ($karyawanList as $karyawan) {
                $this->buatSlipKaryawan(
                    $karyawan, $periode, $bulan, $tahun, $jumlahHariKerja
                );
            }

            $periode->update([
                'status'        => 'final',
                'finalisasi_at' => now(),
                'finalisasi_by' => Auth::id(),
            ]);

            DB::commit();

            return redirect()
                ->route('admin.penggajian.show', $periode->id)
                ->with('success', "Penggajian {$namaPeriode} berhasil diproses.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memproses penggajian: ' . $e->getMessage());
        }
    }

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

    public function detailSlip(SlipGaji $slipGaji)
    {
        $slipGaji->load(['karyawan.user', 'karyawan.komponenGaji', 'periodeGaji']);
        return view('admin.penggajian.slip', compact('slipGaji'));
    }

    private function buatSlipKaryawan(
        Karyawan $karyawan,
        PeriodeGaji $periode,
        int $bulan,
        int $tahun,
        int $jumlahHariKerja
    ): void {
        $kg = $karyawan->komponenGaji;

        $absensi = Absensi::where('karyawan_id', $karyawan->id)
                          ->whereMonth('tanggal', $bulan)
                          ->whereYear('tanggal', $tahun)
                          ->get();

        $totalHadir = $absensi->whereIn('status', ['hadir'])->count();
        $totalTelat = $absensi->where('is_telat', true)->count();
        $totalIzin  = $absensi->whereIn('status', ['izin', 'sakit'])->count();
        $totalCuti  = $absensi->where('status', 'cuti')->count();
        $totalAlfa  = max(0, $jumlahHariKerja - $absensi->count());

        $gajiPokok     = (float) $kg->gaji_pokok;
        
        // Perhitungan untuk SEMUA Karyawan (Tanpa cek mitra)
        $hariDibayar   = $totalHadir + $totalIzin;
        $uangMakan     = (float) ($kg->uang_makan     ?? 35000) * $hariDibayar;
        $uangTransport = (float) ($kg->uang_transport ?? 45000) * $hariDibayar;

        // BPJS
        $potonganBpjsKes = $gajiPokok * ((float) $kg->persen_bpjs_kes / 100);
        $potonganBpjsTk  = $gajiPokok * ((float) $kg->persen_bpjs_tk  / 100);

        // Potongan Telat
        $potonganTelat = 0.0;
        if ($karyawan->isTetap()) {
            $potPerHari   = ($kg->uang_makan ?? 35000) + ($kg->uang_transport ?? 45000);
            $potonganTelat = $totalTelat * $potPerHari;
        }

        // Potongan Cuti
        $potonganIzin = $totalCuti * ($kg->uang_makan ?? 35000);

        // Potongan Alfa
        $potPerHariAlfa = ($kg->uang_makan ?? 35000) + ($kg->uang_transport ?? 45000);
        $potonganAlfa   = $totalAlfa * $potPerHariAlfa;

        $totalPotongan = $potonganBpjsKes + $potonganBpjsTk + $potonganTelat + $potonganIzin + $potonganAlfa;

        $gajiBersih = max(0.0, ($gajiPokok + $uangMakan + $uangTransport) - $totalPotongan);

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
            'potongan_izin'     => $potonganIzin + $potonganAlfa,
            'potongan_bpjs_kes' => $potonganBpjsKes,
            'potongan_bpjs_tk'  => $potonganBpjsTk,
            'total_potongan'    => $totalPotongan,
            'gaji_bersih'       => $gajiBersih,
            'status'            => 'diterbitkan',
            'diterbitkan_at'    => now(),
        ]);
    }

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