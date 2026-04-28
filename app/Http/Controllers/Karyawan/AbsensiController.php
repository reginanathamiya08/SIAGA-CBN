<?php

namespace App\Http\Controllers\Karyawan;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AbsensiController extends Controller
{
    // ─────────────────────────────────────────────────────────────────
    // HALAMAN ABSENSI — tampilkan status + form absen masuk/pulang
    // ─────────────────────────────────────────────────────────────────
    public function index()
    {
        $karyawan = Auth::user()->karyawan;
        $today    = Carbon::today();

        // Cari penempatan aktif karyawan
        $penempatan = $karyawan->penempatanAktif()->with('mitra')->first();

        // Absensi hari ini
        $absensi = Absensi::where('karyawan_id', $karyawan->id)
                          ->whereDate('tanggal', $today)
                          ->first();

        // Riwayat 30 hari terakhir
        $riwayat = Absensi::where('karyawan_id', $karyawan->id)
                          ->whereDate('tanggal', '>=', $today->copy()->subDays(29))
                          ->orderByDesc('tanggal')
                          ->get();

        // Rekap bulan ini
        $rekapBulan = Absensi::where('karyawan_id', $karyawan->id)
                             ->whereMonth('tanggal', $today->month)
                             ->whereYear('tanggal', $today->year)
                             ->selectRaw('status, COUNT(*) as total')
                             ->groupBy('status')
                             ->pluck('total', 'status');

        return view('karyawan.absensi.index', compact(
            'karyawan', 'penempatan', 'absensi', 'riwayat', 'rekapBulan'
        ));
    }

    // ─────────────────────────────────────────────────────────────────
    // ABSEN MASUK
    // ─────────────────────────────────────────────────────────────────
    public function absenMasuk(Request $request)
    {
        $request->validate([
            'latitude'  => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $karyawan = Auth::user()->karyawan;
        $now      = Carbon::now();
        $today    = Carbon::today();

        // ── Cek sudah absen hari ini ──────────────────────────────────
        $existing = Absensi::where('karyawan_id', $karyawan->id)
                           ->whereDate('tanggal', $today)
                           ->first();

        if ($existing && $existing->waktu_masuk) {
            return back()->with('error', 'Kamu sudah melakukan absen masuk hari ini.');
        }

        // ── Cek penempatan aktif ──────────────────────────────────────
        $penempatan = $karyawan->penempatanAktif()->with('mitra')->first();

        if (! $penempatan) {
            return back()->with('error', 'Kamu belum memiliki penempatan aktif. Hubungi admin.');
        }

        $mitra = $penempatan->mitra;

        // ── Validasi IP Public (WAJIB — anti-kecurangan) ──────────────
        // IP otomatis terbaca dari request, karyawan tidak bisa manipulasi
        $ipKaryawan = $request->header('X-Forwarded-For')
                        ? trim(explode(',', $request->header('X-Forwarded-For'))[0])
                        : $request->ip();

        if ($ipKaryawan !== $mitra->ip_public) {
            return back()->with('error',
                "Absensi gagal: Kamu tidak terhubung ke jaringan WiFi {$mitra->nama_mitra}. " .
                "Absensi hanya bisa dilakukan dari jaringan kantor."
            );
        }

        // ── Validasi GPS (Haversine formula) ─────────────────────────
        $jarak = $this->hitungJarak(
            (float) $request->latitude,
            (float) $request->longitude,
            (float) $mitra->latitude,
            (float) $mitra->longitude
        );

        if ($jarak > $mitra->radius_meter) {
            $jarakFormatted = number_format($jarak, 0, ',', '.');
            return back()->with('error',
                "Absensi gagal: Kamu berada {$jarakFormatted} m dari {$mitra->nama_mitra}. " .
                "Maksimal radius adalah {$mitra->radius_meter} m."
            );
        }

        // ── Cek keterlambatan (khusus karyawan tetap) ─────────────────
        $isTelat = false;
        if ($karyawan->isTetap()) {
            $batasTelat = Carbon::today()->setTimeFromTimeString(config('cbn.batas_telat', '08:15:00'));
            $isTelat    = $now->gt($batasTelat);
        }

        // ── Simpan / Update absensi ────────────────────────────────────
        $statusAwal = $isTelat ? 'telat' : 'hadir';

        Absensi::updateOrCreate(
            ['karyawan_id' => $karyawan->id, 'tanggal' => $today],
            [
                'mitra_id'    => $mitra->id,
                'waktu_masuk' => $now,
                'lat_masuk'   => $request->latitude,
                'long_masuk'  => $request->longitude,
                'ip_masuk'    => $ipKaryawan,
                'status'      => $statusAwal,
                'is_telat'    => $isTelat,
            ]
        );

        $pesan = $isTelat
            ? 'Absen masuk berhasil tercatat. Kamu terlambat hari ini.'
            : 'Absen masuk berhasil! Selamat bekerja.';

        return back()->with($isTelat ? 'warning' : 'success', $pesan);
    }

    // ─────────────────────────────────────────────────────────────────
    // ABSEN PULANG
    // ─────────────────────────────────────────────────────────────────
    public function absenPulang(Request $request)
    {
        $request->validate([
            'latitude'  => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $karyawan = Auth::user()->karyawan;
        $now      = Carbon::now();
        $today    = Carbon::today();

        // ── Cek absensi masuk hari ini ────────────────────────────────
        $absensi = Absensi::where('karyawan_id', $karyawan->id)
                          ->whereDate('tanggal', $today)
                          ->first();

        if (! $absensi || ! $absensi->waktu_masuk) {
            return back()->with('error', 'Kamu belum melakukan absen masuk hari ini.');
        }

        if ($absensi->waktu_pulang) {
            return back()->with('error', 'Kamu sudah melakukan absen pulang hari ini.');
        }

        // ── Cek penempatan aktif ──────────────────────────────────────
        $penempatan = $karyawan->penempatanAktif()->with('mitra')->first();
        $mitra      = $penempatan?->mitra ?? \App\Models\Mitra::find($absensi->mitra_id);

        if (! $mitra) {
            return back()->with('error', 'Data mitra tidak ditemukan. Hubungi admin.');
        }

        // ── Validasi IP Public (WAJIB — anti-kecurangan) ──────────────
        $ipKaryawan = $request->header('X-Forwarded-For')
                        ? trim(explode(',', $request->header('X-Forwarded-For'))[0])
                        : $request->ip();

        if ($ipKaryawan !== $mitra->ip_public) {
            return back()->with('error',
                "Absensi pulang gagal: Kamu tidak terhubung ke jaringan WiFi {$mitra->nama_mitra}. " .
                "Absensi hanya bisa dilakukan dari jaringan kantor."
            );
        }

        // ── Validasi GPS ──────────────────────────────────────────────
        $jarak = $this->hitungJarak(
            (float) $request->latitude,
            (float) $request->longitude,
            (float) $mitra->latitude,
            (float) $mitra->longitude
        );

        if ($jarak > $mitra->radius_meter) {
            $jarakFormatted = number_format($jarak, 0, ',', '.');
            return back()->with('error',
                "Absensi pulang gagal: Kamu berada {$jarakFormatted} m dari {$mitra->nama_mitra}. " .
                "Maksimal radius adalah {$mitra->radius_meter} m."
            );
        }

        // ── Update absensi pulang ─────────────────────────────────────
        $absensi->update([
            'waktu_pulang' => $now,
            'lat_pulang'   => $request->latitude,
            'long_pulang'  => $request->longitude,
            'ip_pulang'    => $ipKaryawan,
        ]);

        $durasi = $absensi->durasiMenit();
        $jam    = intdiv($durasi, 60);
        $menit  = $durasi % 60;

        return back()->with('success',
            "Absen pulang berhasil! Durasi kerja hari ini: {$jam} jam {$menit} menit."
        );
    }

    // ─────────────────────────────────────────────────────────────────
    // RIWAYAT ABSENSI (halaman terpisah dengan filter bulan)
    // ─────────────────────────────────────────────────────────────────
    public function riwayat(Request $request)
    {
        $karyawan = Auth::user()->karyawan;
        $today    = Carbon::today();

        $bulan = (int) $request->get('bulan', $today->month);
        $tahun = (int) $request->get('tahun', $today->year);

        $riwayat = Absensi::where('karyawan_id', $karyawan->id)
                          ->whereMonth('tanggal', $bulan)
                          ->whereYear('tanggal', $tahun)
                          ->orderByDesc('tanggal')
                          ->with('mitra')
                          ->get();

        $rekapBulan = $riwayat->groupBy('status')->map->count();

        // Hitung jumlah hari kerja di bulan tersebut (Senin–Sabtu, hari non-minggu)
        $hariKerja = 0;
        $ptr = Carbon::create($tahun, $bulan, 1)->startOfMonth();
        $end = $ptr->copy()->endOfMonth();
        while ($ptr->lte($end)) {
            if (! $ptr->isSunday()) $hariKerja++;
            $ptr->addDay();
        }

        $daftarBulan = collect(range(1, 12))->map(fn($b) => [
            'value' => $b,
            'label' => Carbon::create(null, $b)->translatedFormat('F'),
        ]);

        $daftarTahun = collect(range(now()->year - 2, now()->year));

        return view('karyawan.absensi.riwayat', compact(
            'karyawan', 'riwayat', 'rekapBulan',
            'bulan', 'tahun', 'hariKerja',
            'daftarBulan', 'daftarTahun'
        ));
    }

    // ─────────────────────────────────────────────────────────────────
    // HELPER: Haversine distance (meter)
    // ─────────────────────────────────────────────────────────────────
    private function hitungJarak(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $R    = 6371000; // radius bumi dalam meter
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) ** 2
           + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $R * $c;
    }
}