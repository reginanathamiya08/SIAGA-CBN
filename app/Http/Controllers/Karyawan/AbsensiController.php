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
        \App\Helpers\AttendanceHelper::runAutoAlfaDeduction();

        $karyawan = Auth::user()->karyawan;
        $today    = Carbon::today();
        
        if ($karyawan->role?->slug == 'karyawan_tetap') {
            $mitra = \App\Models\Mitra::where('is_pusat', true)->first();
            $penempatan = $mitra ? (object) ['mitra' => $mitra] : null;
        } else {
            $penempatan = $karyawan->penempatanAktif()->with('mitra')->first();
        }

        $absensi = Absensi::where('user_id', $karyawan->id)
                          ->whereDate('tanggal', $today)
                          ->with('shift')
                          ->first();

        if (!$absensi || $absensi->waktu_pulang) {
            $yesterdayAbsensi = Absensi::where('user_id', $karyawan->id)
                                      ->whereDate('tanggal', Carbon::yesterday())
                                      ->whereNull('waktu_pulang')
                                      ->with('shift')
                                      ->first();
            if ($yesterdayAbsensi && $yesterdayAbsensi->shift?->is_lintas_hari) {
                $absensi = $yesterdayAbsensi;
            }
        }

        $isLiburAtauIzin = false;
        $statusLiburAtauIzin = null;

        $todayAbsensi = Absensi::where('user_id', $karyawan->id)
                               ->whereDate('tanggal', $today)
                               ->first();

        if ($todayAbsensi && in_array($todayAbsensi->status, ['cuti', 'izin', 'sakit', 'dinas_luar'])) {
            $isLiburAtauIzin = true;
            $statusLabels = [
                'cuti' => 'Cuti',
                'izin' => 'Izin',
                'sakit' => 'Sakit',
                'dinas_luar' => 'Dinas Luar',
            ];
            $statusLiburAtauIzin = $statusLabels[$todayAbsensi->status] ?? ucfirst($todayAbsensi->status);
        }

        $bolehPulang = true;
        $pesanBelumPulang = null;
        
        if ($absensi && !$absensi->waktu_pulang && !$isLiburAtauIzin) {
            $jamSelesaiStr = null;
            if ($absensi->shift) {
                $jamSelesaiStr = $absensi->shift->jam_selesai;
                $jamSelesai = Carbon::parse($absensi->tanggal->format('Y-m-d') . ' ' . $jamSelesaiStr);
                if ($absensi->shift->is_lintas_hari) $jamSelesai->addDay();
            } else {
                $mitra = $karyawan->penempatanAktif()->with('mitra')->first()?->mitra;
                if ($mitra && $mitra->jam_pulang) {
                    $jamSelesaiStr = $mitra->jam_pulang;
                    $jamSelesai = Carbon::parse($absensi->tanggal->format('Y-m-d') . ' ' . $jamSelesaiStr);
                }
            }

            if ($jamSelesaiStr) {
                $batasPulang = $jamSelesai->copy()->subMinutes(15);
                $bolehPulang = Carbon::now()->gte($batasPulang);
                
                if (!$bolehPulang) {
                    $jamTampil = substr($jamSelesaiStr, 0, 5);
                    $pesanBelumPulang = "Jam kerja selesai jam {$jamTampil}. Kamu baru bisa absen pulang 15 menit sebelum jam tersebut.";
                }
            }
        }

        $riwayat = Absensi::where('user_id', $karyawan->id)
                          ->whereDate('tanggal', '>=', $today->copy()->subDays(29))
                          ->orderByDesc('tanggal')
                          ->get();

        $rekapBulan = Absensi::where('user_id', $karyawan->id)
                             ->whereMonth('tanggal', $today->month)
                             ->whereYear('tanggal', $today->year)
                             ->selectRaw('status, COUNT(*) as total')
                             ->groupBy('status')
                             ->pluck('total', 'status');

        return view('karyawan.absensi.index', compact(
            'karyawan', 'penempatan', 'absensi', 'riwayat', 'rekapBulan', 'bolehPulang', 'pesanBelumPulang',
            'isLiburAtauIzin', 'statusLiburAtauIzin'
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

        $statusHariIni = Absensi::where('user_id', $karyawan->id)
                                ->whereDate('tanggal', $today)
                                ->first();
        if ($statusHariIni && in_array($statusHariIni->status, ['cuti', 'izin', 'sakit', 'dinas_luar'])) {
            return back()->with('error', "Absensi ditolak. Hari ini status Anda tercatat sebagai " . strtoupper($statusHariIni->status) . ".");
        }

        if ($statusHariIni && in_array($statusHariIni->status, ['hadir', 'telat']) && $statusHariIni->waktu_masuk) {
            return back()->with('error', 'Kamu sudah absen masuk hari ini.');
        }

        if ($karyawan->role?->slug == 'karyawan_tetap') {
            $mitra = \App\Models\Mitra::where('is_pusat', true)->first();
            if (!$mitra) return back()->with('error', 'Data kantor pusat (PT CBN) belum diatur oleh admin.');
        } else {
            $penempatan = $karyawan->penempatanAktif()->with('mitra')->first();
            if (!$penempatan) return back()->with('error', 'Penempatan aktif tidak ditemukan.');
            $mitra = $penempatan->mitra;
        }

        // ✅ VALIDASI GPS
        $jarak = $this->hitungJarak(
            (float)$request->latitude, (float)$request->longitude,
            (float)$mitra->latitude, (float)$mitra->longitude
        );

        if ($jarak > $mitra->radius_meter) {
            return back()->with('error', "Lokasi tidak valid. Kamu berada " . number_format($jarak, 0) . "m dari " . $mitra->nama_mitra . ". Pastikan kamu berada di dalam area kantor.");
        }

        // ✅ VALIDASI IP PUBLIC (Smart Validation: IPv4 & Smart IPv6 Prefix)
        $ipKaryawan = $request->ip();
        $ipMitra    = $mitra->ip_public;

        if ($ipMitra) {
            $allowedIps = array_map('trim', explode(',', $ipMitra));
            $isMatched = false;

            foreach ($allowedIps as $allowed) {
                // Jika input di database adalah IPv6 (biasanya mengandung banyak titik dua)
                if (filter_var($allowed, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
                    // Ambil 4 kelompok pertama (Prefix /64) dari IP Karyawan dan IP Database
                    $partsKaryawan = explode(':', $ipKaryawan);
                    $partsAllowed  = explode(':', $allowed);
                    
                    if (count($partsKaryawan) >= 4 && count($partsAllowed) >= 4) {
                        $prefixKaryawan = implode(':', array_slice($partsKaryawan, 0, 4));
                        $prefixAllowed  = implode(':', array_slice($partsAllowed, 0, 4));
                        
                        if ($prefixKaryawan === $prefixAllowed) {
                            $isMatched = true;
                            break;
                        }
                    }
                } 
                // Cek IPv4 dengan Wildcard (misal: 182.9.200.*)
                else if (str_contains($allowed, '*')) {
                    $prefixAllowed = str_replace('*', '', $allowed);
                    if (str_starts_with($ipKaryawan, $prefixAllowed)) {
                        $isMatched = true;
                        break;
                    }
                }
                // Cek IPv4 standar (Exact Match)
                else if ($ipKaryawan === $allowed) {
                    $isMatched = true;
                    break;
                }
            }
            
            if (!$isMatched) {
                return back()->with('error', "Absensi ditolak. Gunakan jaringan WiFi kantor " . $mitra->nama_mitra . " untuk melakukan absensi. (IP terdeteksi: {$ipKaryawan})");
            }
        }

        $shiftTerdeteksi = null;
        if ($karyawan->is_shift) {
            $shifts = Shift::where('mitra_id', $mitra->id)->get();
            foreach ($shifts as $s) {
                if ($s->isInWindow($now)) {
                    $shiftTerdeteksi = $s;
                    break;
                }
            }
        }

        $jamMulai  = null;
        $toleransi = 0;

        if ($shiftTerdeteksi) {
            $jamMulai  = Carbon::today()->setTimeFromTimeString($shiftTerdeteksi->jam_mulai);
            $toleransi = $shiftTerdeteksi->toleransi_menit;
        } else {
            if ($karyawan->is_shift) {
                return back()->with('error', 'Waktu absensi tidak sesuai jadwal shift Satpam yang tersedia. Silakan hubungi Admin.');
            }

            if (!$mitra->jam_masuk) {
                return back()->with('error', 'Jadwal kerja mitra belum diatur. Silakan hubungi Admin.');
            }
            
            $jamMulai  = Carbon::today()->setTimeFromTimeString($mitra->jam_masuk);
            $toleransi = 15; 
        }

        $existing = Absensi::where('user_id', $karyawan->id)
                           ->whereDate('tanggal', $today)
                           ->when($shiftTerdeteksi, function($q) use ($shiftTerdeteksi) {
                               return $q->where('shift_id', $shiftTerdeteksi->id);
                           }, function($q) {
                               return $q->whereNull('shift_id');
                           })
                           ->first();

        if ($existing && $existing->waktu_masuk) {
            return back()->with('error', 'Kamu sudah absen masuk hari ini.');
        }

        $batasToleransi = $jamMulai->copy()->addMinutes($toleransi);
        $isTelat        = $now->gt($batasToleransi);
        $status         = $isTelat ? 'telat' : 'hadir';

        if ($existing) {
            $wasAlfa = ($existing->status === 'alfa');
            
            $existing->update([
                'waktu_masuk' => $now,
                'lat_masuk'   => $request->latitude,
                'long_masuk'  => $request->longitude,
                'status'      => $status,
                'is_telat'    => $isTelat,
                'ip_masuk'    => $ipKaryawan,
            ]);

            if ($wasAlfa) {
                $kuota = $karyawan->kuotaPerizinanTahunIni();
                if ($kuota && $kuota->terpakai > 0) {
                    $kuota->terpakai = max(0, $kuota->terpakai - 1);
                    $kuota->sisa     = min($kuota->kuota_total, $kuota->sisa + 1);
                    $kuota->save();
                }
            }
        } else {
            Absensi::create([
                'user_id'     => $karyawan->id,
                'mitra_id'    => $mitra->id,
                'shift_id'    => $shiftTerdeteksi?->id,
                'tanggal'     => $today,
                'waktu_masuk' => $now,
                'lat_masuk'   => $request->latitude,
                'long_masuk'  => $request->longitude,
                'status'      => $status,
                'is_telat'    => $isTelat,
                'ip_masuk'    => $ipKaryawan,
            ]);
        }

        return back()->with($isTelat ? 'warning' : 'success', 'Absen masuk berhasil.' . ($isTelat ? ' (Terlambat)' : ''));
    }

    public function absenPulang(Request $request)
    {
        $request->validate([
            'latitude'  => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);
        
        $karyawan = Auth::user()->karyawan;
        $now      = Carbon::now();
        $today    = Carbon::today();

        $statusHariIni = Absensi::where('user_id', $karyawan->id)
                                ->whereDate('tanggal', $today)
                                ->first();
        if ($statusHariIni && !in_array($statusHariIni->status, ['hadir', 'telat'])) {
            return back()->with('error', "Absensi ditolak. Hari ini status Anda tercatat sebagai " . strtoupper($statusHariIni->status) . ".");
        }

        $absensi = Absensi::where('user_id', $karyawan->id)
                          ->where('waktu_pulang', null)
                          ->with('shift')
                          ->latest()
                          ->first();

        if (!$absensi) {
            return back()->with('error', 'Belum melakukan absen masuk.');
        }

        $shift      = $absensi->shift;
        $jamSelesai = null;

        if ($shift) {
            $jamSelesai = Carbon::parse($absensi->tanggal->format('Y-m-d') . ' ' . $shift->jam_selesai);
            if ($shift->is_lintas_hari) $jamSelesai->addDay();
        } else {
            if ($karyawan->role?->slug == 'karyawan_tetap') {
                $mitra = \App\Models\Mitra::where('is_pusat', true)->first();
            } else {
                $penempatan = $karyawan->penempatanAktif()->with('mitra')->first();
                $mitra      = $penempatan?->mitra;
            }

            if ($mitra && $mitra->jam_pulang) {
                $jamSelesai = Carbon::parse($absensi->tanggal->format('Y-m-d') . ' ' . $mitra->jam_pulang);
            }
        }

        if ($jamSelesai && $now->lt($jamSelesai->copy()->subMinutes(15))) {
            $jamStr = $shift ? $shift->jam_selesai : substr($mitra->jam_pulang, 0, 5);
            return back()->with('error', "Belum waktunya pulang. Jam kerja berakhir jam {$jamStr}.");
        }

        if ($karyawan->role?->slug == 'karyawan_tetap') {
            $mitra = \App\Models\Mitra::where('is_pusat', true)->first();
        } else {
            $penempatan = $karyawan->penempatanAktif()->with('mitra')->first();
            $mitra      = $penempatan->mitra;
        }

        // ✅ VALIDASI GPS
        $jarak = $this->hitungJarak(
            (float)$request->latitude, (float)$request->longitude,
            (float)$mitra->latitude, (float)$mitra->longitude
        );

        if ($jarak > $mitra->radius_meter) {
            return back()->with('error', "Lokasi tidak valid untuk absen pulang. Pastikan kamu berada di dalam area kantor.");
        }

        // ✅ VALIDASI IP PUBLIC (Smart Validation: IPv4 & Smart IPv6 Prefix)
        $ipKaryawan = $request->ip();
        $ipMitra    = $mitra->ip_public;

        if ($ipMitra) {
            $allowedIps = array_map('trim', explode(',', $ipMitra));
            $isMatched = false;

            foreach ($allowedIps as $allowed) {
                if (filter_var($allowed, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
                    $partsKaryawan = explode(':', $ipKaryawan);
                    $partsAllowed  = explode(':', $allowed);
                    
                    if (count($partsKaryawan) >= 4 && count($partsAllowed) >= 4) {
                        $prefixKaryawan = implode(':', array_slice($partsKaryawan, 0, 4));
                        $prefixAllowed  = implode(':', array_slice($partsAllowed, 0, 4));
                        
                        if ($prefixKaryawan === $prefixAllowed) {
                            $isMatched = true;
                            break;
                        }
                    }
                } 
                else if ($ipKaryawan === $allowed) {
                    $isMatched = true;
                    break;
                }
            }
            
            if (!$isMatched) {
                return back()->with('error', "Absensi pulang ditolak. Gunakan jaringan WiFi kantor " . $mitra->nama_mitra . " untuk melakukan absensi. (IP terdeteksi: {$ipKaryawan})");
            }
        }

        $absensi->update([
            'waktu_pulang' => $now,
            'lat_pulang'   => $request->latitude,
            'long_pulang'  => $request->longitude,
            'ip_pulang'    => $ipKaryawan,
        ]);

        return back()->with('success', 'Absen pulang berhasil. Selamat istirahat!');
    }

    public function riwayat(Request $request)
    {
        $karyawan = Auth::user()->karyawan;
        $bulan    = $request->get('bulan', now()->month);
        $tahun    = $request->get('tahun', now()->year);

        $riwayat = Absensi::where('user_id', $karyawan->id)
                          ->whereMonth('tanggal', $bulan)
                          ->whereYear('tanggal', $tahun)
                          ->orderByDesc('tanggal')
                          ->with(['mitra', 'shift'])
                          ->get();

        $rekapBulan = $riwayat->groupBy('status')->map->count();

        // Daftar bulan untuk filter
        $daftarBulan = [
            ['value' => 1,  'label' => 'Januari'],
            ['value' => 2,  'label' => 'Februari'],
            ['value' => 3,  'label' => 'Maret'],
            ['value' => 4,  'label' => 'April'],
            ['value' => 5,  'label' => 'Mei'],
            ['value' => 6,  'label' => 'Juni'],
            ['value' => 7,  'label' => 'Juli'],
            ['value' => 8,  'label' => 'Agustus'],
            ['value' => 9,  'label' => 'September'],
            ['value' => 10, 'label' => 'Oktober'],
            ['value' => 11, 'label' => 'November'],
            ['value' => 12, 'label' => 'Desember'],
        ];

        // Daftar tahun (5 tahun terakhir)
        $daftarTahun = range(now()->year, now()->year - 5);

        // Hitung jumlah hari kerja (Senin - Jumat, mengecualikan weekend & libur nasional)
        $dateStart = Carbon::createFromDate($tahun, $bulan, 1);
        $dateEnd = $dateStart->copy()->endOfMonth();
        $hariKerja = \App\Helpers\AttendanceHelper::countWorkingDays($dateStart, $dateEnd);

        return view('karyawan.absensi.riwayat', compact(
            'karyawan', 'riwayat', 'rekapBulan', 'bulan', 'tahun', 
            'daftarBulan', 'daftarTahun', 'hariKerja'
        ));
    }

    private function hitungJarak($lat1, $lon1, $lat2, $lon2)
    {
        $R   = 6371000;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a   = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon/2) * sin($dLon/2);
        $c   = 2 * atan2(sqrt($a), sqrt(1-$a));
        return $R * $c;
    }
}
