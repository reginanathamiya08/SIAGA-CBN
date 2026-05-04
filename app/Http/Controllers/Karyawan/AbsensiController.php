<?php

namespace App\Http\Controllers\Karyawan;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Shift;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AbsensiController extends Controller
{
    public function index()
    {
        $karyawan = Auth::user()->karyawan;
        $today    = Carbon::today();
        $penempatan = $karyawan->penempatanAktif()->with('mitra')->first();

        $absensi = Absensi::where('karyawan_id', $karyawan->id)
                          ->whereDate('tanggal', $today)
                          ->with('shift')
                          ->first();

        if (!$absensi || $absensi->waktu_pulang) {
            $yesterdayAbsensi = Absensi::where('karyawan_id', $karyawan->id)
                                      ->whereDate('tanggal', Carbon::yesterday())
                                      ->whereNull('waktu_pulang')
                                      ->with('shift')
                                      ->first();
            if ($yesterdayAbsensi && $yesterdayAbsensi->shift?->is_lintas_hari) {
                $absensi = $yesterdayAbsensi;
            }
        }

        // Cek apakah sudah boleh pulang (minimal 15 menit sebelum shift selesai)
        $bolehPulang = true;
        $pesanBelumPulang = null;
        
        if ($absensi && $absensi->shift && !$absensi->waktu_pulang) {
            $jamSelesai = Carbon::parse($absensi->tanggal->format('Y-m-d') . ' ' . $absensi->shift->jam_selesai);
            if ($absensi->shift->is_lintas_hari) $jamSelesai->addDay();
            
            $batasPulang = $jamSelesai->copy()->subMinutes(15);
            $bolehPulang = Carbon::now()->gte($batasPulang);
            
            if (!$bolehPulang) {
                $pesanBelumPulang = "Shift selesai jam {$absensi->shift->jam_selesai}. Kamu baru bisa absen pulang 15 menit sebelum jam tersebut.";
            }
        }

        $riwayat = Absensi::where('karyawan_id', $karyawan->id)
                          ->whereDate('tanggal', '>=', $today->copy()->subDays(29))
                          ->orderByDesc('tanggal')
                          ->get();

        $rekapBulan = Absensi::where('karyawan_id', $karyawan->id)
                             ->whereMonth('tanggal', $today->month)
                             ->whereYear('tanggal', $today->year)
                             ->selectRaw('status, COUNT(*) as total')
                             ->groupBy('status')
                             ->pluck('total', 'status');

        return view('karyawan.absensi.index', compact(
            'karyawan', 'penempatan', 'absensi', 'riwayat', 'rekapBulan', 'bolehPulang', 'pesanBelumPulang'
        ));
    }

    public function absenMasuk(Request $request)
    {
        $request->validate([
            'latitude'  => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $karyawan = Auth::user()->karyawan;
        $now      = Carbon::now();
        $today    = Carbon::today();

        $penempatan = $karyawan->penempatanAktif()->with('mitra')->first();
        if (!$penempatan) return back()->with('error', 'Penempatan aktif tidak ditemukan.');
        
        $mitra = $penempatan->mitra;
        $jarak = $this->hitungJarak((float)$request->latitude, (float)$request->longitude, (float)$mitra->latitude, (float)$mitra->longitude);

        if ($jarak > $mitra->radius_meter) {
            return back()->with('error', "Lokasi tidak valid. Kamu berada " . number_format($jarak, 0) . "m dari " . $mitra->nama_mitra);
        }

        $shifts = Shift::where('mitra_id', $mitra->id)->get();
        $shiftTerdeteksi = null;

        foreach ($shifts as $s) {
            if ($s->isInWindow($now)) {
                $shiftTerdeteksi = $s;
                break;
            }
        }

        if (!$shiftTerdeteksi) {
            return back()->with('error', 'Waktu absensi tidak sesuai jadwal shift manapun.');
        }

        $existing = Absensi::where('karyawan_id', $karyawan->id)
                           ->whereDate('tanggal', $today)
                           ->where('shift_id', $shiftTerdeteksi->id)
                           ->first();

        if ($existing) {
            return back()->with('error', 'Kamu sudah absen masuk hari ini.');
        }

        $jamMulai = Carbon::today()->setTimeFromTimeString($shiftTerdeteksi->jam_mulai);
        $batasToleransi = $jamMulai->copy()->addMinutes($shiftTerdeteksi->toleransi_menit);
        $isTelat = $now->gt($batasToleransi);
        $status  = $isTelat ? 'telat' : 'hadir';

        Absensi::create([
            'karyawan_id' => $karyawan->id,
            'mitra_id'    => $mitra->id,
            'shift_id'    => $shiftTerdeteksi->id,
            'tanggal'     => $today,
            'waktu_masuk' => $now,
            'lat_masuk'   => $request->latitude,
            'long_masuk'  => $request->longitude,
            'status'      => $status,
            'is_telat'    => $isTelat,
            'ip_masuk'    => $request->ip()
        ]);

        return back()->with($isTelat ? 'warning' : 'success', 'Absen masuk berhasil.');
    }

    public function absenPulang(Request $request)
    {
        $request->validate(['latitude' => 'required|numeric', 'longitude' => 'required|numeric']);
        
        $karyawan = Auth::user()->karyawan;
        $now      = Carbon::now();

        $absensi = Absensi::where('karyawan_id', $karyawan->id)
                          ->where('waktu_pulang', null)
                          ->with('shift')
                          ->latest()
                          ->first();

        if (!$absensi) {
            return back()->with('error', 'Belum melakukan absen masuk.');
        }

        $shift = $absensi->shift;
        if ($shift) {
            $jamSelesai = Carbon::parse($absensi->tanggal->format('Y-m-d') . ' ' . $shift->jam_selesai);
            if ($shift->is_lintas_hari) $jamSelesai->addDay();
            
            if ($now->lt($jamSelesai->copy()->subMinutes(15))) {
                return back()->with('error', "Belum waktunya pulang. Shift baru selesai jam {$shift->jam_selesai}.");
            }
        }

        $penempatan = $karyawan->penempatanAktif()->with('mitra')->first();
        $mitra = $penempatan->mitra;
        $jarak = $this->hitungJarak((float)$request->latitude, (float)$request->longitude, (float)$mitra->latitude, (float)$mitra->longitude);

        if ($jarak > $mitra->radius_meter) {
            return back()->with('error', "Lokasi tidak valid untuk absen pulang.");
        }

        $absensi->update([
            'waktu_pulang' => $now,
            'lat_pulang'   => $request->latitude,
            'long_pulang'  => $request->longitude,
            'ip_pulang'    => $request->ip()
        ]);

        return back()->with('success', 'Absen pulang berhasil. Selamat istirahat!');
    }

    public function riwayat(Request $request)
    {
        $karyawan = Auth::user()->karyawan;
        $bulan = $request->get('bulan', now()->month);
        $tahun = $request->get('tahun', now()->year);

        $riwayat = Absensi::where('karyawan_id', $karyawan->id)
                          ->whereMonth('tanggal', $bulan)
                          ->whereYear('tanggal', $tahun)
                          ->orderByDesc('tanggal')
                          ->with(['mitra', 'shift'])
                          ->get();

        $rekapBulan = $riwayat->groupBy('status')->map->count();

        return view('karyawan.absensi.riwayat', compact('karyawan', 'riwayat', 'rekapBulan', 'bulan', 'tahun'));
    }

    private function hitungJarak($lat1, $lon1, $lat2, $lon2)
    {
        $R = 6371000;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon/2) * sin($dLon/2);
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        return $R * $c;
    }
}