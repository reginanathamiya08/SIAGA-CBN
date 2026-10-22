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
                $bolehPulang = Carbon::now()->gte($jamSelesai);
                
                if (!$bolehPulang) {
                    $jamTampil = substr($jamSelesaiStr, 0, 5);
                    $pesanBelumPulang = "Jam kerja selesai jam {$jamTampil} WIB. Kamu baru bisa absen pulang tepat pada jam tersebut.";
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

        // Evaluasi Track Record Kontrak untuk Karyawan Kontrak
        $evaluasiKontrak = null;
        if ($karyawan->role?->slug === 'karyawan_kontrak') {
            $hariKerjaBulanIni = \App\Helpers\AttendanceHelper::countWorkingDays($today->copy()->startOfMonth(), $today->copy()->endOfMonth());
            $hadirBulanIni     = ($rekapBulan['hadir'] ?? 0) + ($rekapBulan['telat'] ?? 0);
            $persenBulanIni    = $hariKerjaBulanIni > 0 ? round(($hadirBulanIni / $hariKerjaBulanIni) * 100, 1) : 0;
            
            $statusEval = match(true) {
                $persenBulanIni >= 90 => 'Sangat Layak Perpanjang Kontrak',
                $persenBulanIni >= 80 => 'Layak Perpanjang Kontrak',
                default               => 'Perlu Evaluasi Kehadiran'
            };

            $badgeEval = match(true) {
                $persenBulanIni >= 90 => 'bg-emerald-500 text-white',
                $persenBulanIni >= 80 => 'bg-blue-600 text-white',
                default               => 'bg-rose-600 text-white'
            };

            $evaluasiKontrak = [
                'hari_kerja'   => $hariKerjaBulanIni,
                'hadir'        => $hadirBulanIni,
                'alfa'         => $rekapBulan['alfa'] ?? 0,
                'telat'        => Absensi::where('user_id', $karyawan->id)->whereMonth('tanggal', $today->month)->whereYear('tanggal', $today->year)->where('is_telat', true)->count(),
                'persen'       => $persenBulanIni,
                'status'       => $statusEval,
                'badge'        => $badgeEval,
            ];
        }

        return view('karyawan.absensi.index', compact(
            'karyawan', 'penempatan', 'absensi', 'riwayat', 'rekapBulan', 'bolehPulang', 'pesanBelumPulang',
            'isLiburAtauIzin', 'statusLiburAtauIzin', 'evaluasiKontrak'
        ));
    }

    public function absenMasuk(Request $request)
    {
        $request->validate([
            'latitude'  => 'required|numeric',
            'longitude' => 'required|numeric',
            'accuracy'  => 'nullable|numeric',
        ]);

        $karyawan = Auth::user()->karyawan;
        $now      = Carbon::now();
        $today    = Carbon::today();

        $accuracy = $request->filled('accuracy') ? (float) $request->accuracy : null;
        if ($accuracy === null || $accuracy > 100) {
            return back()->with('error', 'Sinyal GPS tidak akurat (akurasi: ' . ($accuracy ?? 'tidak terdeteksi') . 'm). Pastikan GPS aktif dan coba di area terbuka.');
        }

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

        // ✅ VALIDASI IP PUBLIC (WAJIB — tidak ada bypass jika kosong)
        $ipKaryawan = $this->normalizeIp($request->ip());
        $ipMitra    = $mitra->ip_public;

        if (empty(trim((string) $ipMitra))) {
            return back()->with('error', "Absensi ditolak. Konfigurasi IP Publik untuk mitra " . $mitra->nama_mitra . " belum diatur oleh Admin. Hubungi Admin untuk melengkapi data ini sebelum melakukan presensi.");
        }

        if (!$this->isIpAllowed($ipKaryawan, $ipMitra)) {
            return back()->with('error', "Absensi ditolak. Gunakan jaringan WiFi kantor " . $mitra->nama_mitra . " untuk melakukan absensi. (IP terdeteksi: {$ipKaryawan})");
        }

        // ✅ CROSS-CHECK IP GEOLOCATION vs GPS (mitigasi VPN/proxy)
        $ipGeoError = $this->cekIpGeolocation($ipKaryawan, (float) $mitra->latitude, (float) $mitra->longitude);
        if ($ipGeoError) {
            return back()->with('error', $ipGeoError);
        }

        $shiftTerdeteksi = null;
        if ($karyawan->is_shift) {
            $shifts = Shift::where('mitra_id', $mitra->id)->get();
            $kandidat = $shifts->filter(fn($s) => $s->isInWindow($now));

            if ($kandidat->count() > 1) {
                $nowMinutes = \Carbon\Carbon::parse($now)->hour * 60 + \Carbon\Carbon::parse($now)->minute;
                $shiftTerdeteksi = $kandidat->sortBy(function ($s) use ($nowMinutes) {
                    $jamMulai = \Carbon\Carbon::parse($s->jam_mulai);
                    $jamMulaiMenit = $jamMulai->hour * 60 + $jamMulai->minute;
                    $selisih = abs($nowMinutes - $jamMulaiMenit);
                    return min($selisih, 1440 - $selisih);
                })->first();
            } else {
                $shiftTerdeteksi = $kandidat->first();
            }
        }

        $jamMulai  = null;
        $toleransi = 0;

        if ($shiftTerdeteksi) {
            $jamMulai   = Carbon::today()->setTimeFromTimeString($shiftTerdeteksi->jam_mulai);
            $jamSelesai = Carbon::today()->setTimeFromTimeString($shiftTerdeteksi->jam_selesai);
            if ($jamSelesai->lt($jamMulai)) {
                $jamSelesai->addDay();
            }
            $toleransi  = $shiftTerdeteksi->toleransi_menit;
        } else {
            if ($karyawan->is_shift) {
                return back()->with('error', 'Waktu absensi tidak sesuai jadwal shift Satpam yang tersedia. Silakan hubungi Admin.');
            }

            if (!$mitra->jam_masuk || !$mitra->jam_pulang) {
                return back()->with('error', 'Jadwal kerja mitra belum diatur. Silakan hubungi Admin.');
            }
            
            $jamMulai   = Carbon::today()->setTimeFromTimeString($mitra->jam_masuk);
            $jamSelesai = Carbon::today()->setTimeFromTimeString($mitra->jam_pulang);
            $toleransi  = 15; 
        }

        if (!$shiftTerdeteksi) {
            $cutoff14 = Carbon::today()->setTime(14, 0, 0);
            if ($now->gte($cutoff14)) {
                return back()->with('error', 'Absen masuk ditolak. Batas maksimal waktu absen masuk adalah jam 14:00 WIB (Jam 2 Siang).');
            }
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

        $batasToleransi = $jamMulai->copy()->addMinutes((int) $toleransi);
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

        return back()->with('absen_popup', [
            'type'     => 'masuk',
            'title'    => 'Absen Masuk Berhasil!',
            'waktu'    => 'Pukul ' . $now->format('H:i') . ' WIB',
            'is_telat' => $isTelat,
            'status'   => $isTelat ? 'Terlambat (Telat)' : 'Tepat Waktu (Hadir)',
        ]);
    }

    public function absenPulang(Request $request)
    {
        $request->validate([
            'latitude'  => 'required|numeric',
            'longitude' => 'required|numeric',
            'accuracy'  => 'nullable|numeric',
        ]);

        $accuracy = $request->filled('accuracy') ? (float) $request->accuracy : null;
        if ($accuracy === null || $accuracy > 100) {
            return back()->with('error', 'Sinyal GPS tidak akurat (akurasi: ' . ($accuracy ?? 'tidak terdeteksi') . 'm). Pastikan GPS aktif dan coba di area terbuka.');
        }

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

        if ($jamSelesai && $now->lt($jamSelesai)) {
            $jamStr = $shift ? $shift->jam_selesai : substr($mitra->jam_pulang, 0, 5);
            return back()->with('error', "Belum waktunya pulang. Jam kerja berakhir jam {$jamStr} WIB.");
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

        // ✅ VALIDASI IP PUBLIC (WAJIB — tidak ada bypass jika kosong)
        $ipKaryawan = $this->normalizeIp($request->ip());
        $ipMitra    = $mitra->ip_public;

        if (empty(trim((string) $ipMitra))) {
            return back()->with('error', "Absensi pulang ditolak. Konfigurasi IP Publik untuk mitra " . $mitra->nama_mitra . " belum diatur oleh Admin. Hubungi Admin untuk melengkapi data ini.");
        }

        if (!$this->isIpAllowed($ipKaryawan, $ipMitra)) {
            return back()->with('error', "Absensi pulang ditolak. Gunakan jaringan WiFi kantor " . $mitra->nama_mitra . " untuk melakukan absensi. (IP terdeteksi: {$ipKaryawan})");
        }

        // ✅ CROSS-CHECK IP GEOLOCATION vs GPS (mitigasi VPN/proxy)
        $ipGeoError = $this->cekIpGeolocation($ipKaryawan, (float) $mitra->latitude, (float) $mitra->longitude);
        if ($ipGeoError) {
            return back()->with('error', $ipGeoError);
        }

        $absensi->update([
            'waktu_pulang' => $now,
            'lat_pulang'   => $request->latitude,
            'long_pulang'  => $request->longitude,
            'ip_pulang'    => $ipKaryawan,
        ]);

        return back()->with('absen_popup', [
            'type'     => 'pulang',
            'title'    => 'Absen Pulang Berhasil!',
            'waktu'    => 'Pukul ' . $now->format('H:i') . ' WIB',
            'is_telat' => false,
            'status'   => 'Selesai Bekerja',
        ]);
    }

    // Helper cross-check lokasi geografis IP karyawan (mitigasi VPN/proxy).
    // Mengembalikan pesan error (string) jika terdeteksi mencurigakan, atau null jika aman/tidak dapat dicek.
    private function cekIpGeolocation(string $ipKaryawan, float $mitraLat, float $mitraLon): ?string
    {
        // IP privat/lokal (jaringan internal, localhost, testing) tidak bisa di-geolocate publik, lewati saja.
        if (!filter_var($ipKaryawan, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            return null;
        }

        try {
            $response = \Illuminate\Support\Facades\Http::timeout(3)
                ->get("http://ip-api.com/json/{$ipKaryawan}", ['fields' => 'status,lat,lon,isp,proxy']);

            if (!$response->successful()) {
                return null; // API gagal/timeout -> fail-open, jangan blokir absensi karena API pihak ketiga down
            }

            $data = $response->json();
            if (($data['status'] ?? null) !== 'success') {
                return null;
            }

            // Jika penyedia data sudah menandai IP ini sebagai proxy/VPN diketahui
            if (($data['proxy'] ?? false) === true) {
                return "Absensi ditolak. Terdeteksi penggunaan VPN/proxy (ISP: " . ($data['isp'] ?? 'tidak diketahui') . ").";
            }

            // Bandingkan jarak lokasi IP (menurut basis data publik) terhadap lokasi mitra
            $jarakIp = $this->hitungJarak($data['lat'], $data['lon'], $mitraLat, $mitraLon);
            if ($jarakIp > 100000) { // lebih dari 100 km dianggap mencurigakan
                return "Absensi ditolak. Lokasi jaringan (IP) terdeteksi " . number_format($jarakIp / 1000, 0) . " km dari kantor. Kemungkinan menggunakan VPN.";
            }
        } catch (\Exception $e) {
            return null; // fail-open jika request gagal total
        }

        return null;
    }

    // Helper untuk validasi IP Public Mitra (IPv4 exact & wildcard + IPv6 prefix)
    private function isIpAllowed(string $ipKaryawan, string $ipMitraConfig): bool
    {
        $allowedIps = array_map('trim', explode(',', $ipMitraConfig));

        foreach ($allowedIps as $allowed) {
            if (empty($allowed)) continue;

            // 1. Exact match
            if ($ipKaryawan === $allowed) {
                return true;
            }

            // 2. Wildcard match — contoh: 182.9.200.* atau 2404:c0:d001:*
            if (str_contains($allowed, '*')) {
                $prefix = str_replace('*', '', $allowed);
                if (str_starts_with($ipKaryawan, $prefix)) {
                    return true;
                }
            }

            // 3. IPv6 prefix match — cocokkan minimal 3 hextet pertama (subnet kantor)
            if (str_contains($allowed, ':') && str_contains($ipKaryawan, ':')) {
                $pK = explode(':', $ipKaryawan);
                $pA = array_filter(explode(':', str_replace('*', '', $allowed)), fn($v) => $v !== '');
                $count = min(count($pK), count($pA));
                if ($count >= 3) {
                    $prefixK = implode(':', array_slice($pK, 0, min(4, $count)));
                    $prefixA = implode(':', array_slice($pA, 0, min(4, $count)));
                    if (!empty($prefixA) && str_starts_with($prefixK, $prefixA)) {
                        return true;
                    }
                }
            }
        }

        return false;
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

    /**
     * Normalisasi IP address:
     * - Jika IPv4-mapped IPv6 (::ffff:x.x.x.x), ubah ke IPv4 biasa
     * - Jika pure IPv6 atau IPv4, kembalikan apa adanya
     */
    private function normalizeIp(string $ip): string
    {
        // Hapus bracket jika ada, misal: [::1] -> ::1
        $ip = trim($ip, '[]');

        // IPv4-mapped IPv6: ::ffff:x.x.x.x atau ::ffff:xx.xx atau 0:0:0:0:0:ffff:...
        if (preg_match('/^::ffff:((\d{1,3}\.){3}\d{1,3})$/i', $ip, $matches)) {
            return $matches[1]; // Kembalikan bagian IPv4-nya saja
        }

        return $ip;
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