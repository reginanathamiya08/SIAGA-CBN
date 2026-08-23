<?php

namespace App\Helpers;

use App\Models\Absensi;
use App\Models\DetailPerizinan;
use App\Models\KuotaPerizinan;
use App\Models\User;
use App\Models\Mitra;
use App\Models\Configuration;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class AttendanceHelper
{
    /**
     * Mengambil daftar hari libur nasional Indonesia dari API dan menyimpannya di cache.
     */
    /**
     * Mengambil daftar hari libur nasional Indonesia dari API dan menyimpannya di cache.
     */
    public static function getIndonesianHolidays(int $year): array
    {
        return Cache::remember('indonesian_holidays_' . $year, 86400, function () use ($year) {
            try {
                // Timeout singkat (1 detik) agar tidak menghambat loading halaman jika API luar lambat/down
                $response = Http::withoutVerifying()->timeout(1)->get("https://libur.deno.dev/api?year={$year}");
                if ($response->successful()) {
                    return $response->json();
                }
            } catch (\Exception $e) {
                // Ignore exception and use fallback
            }

            // Fallback daftar libur nasional Indonesia standar jika API eksternal gagal/timeout
            return [
                ['date' => "{$year}-01-01", 'name' => 'Tahun Baru Masehi'],
                ['date' => "{$year}-01-27", 'name' => 'Isra Mikraj Nabi Muhammad SAW'],
                ['date' => "{$year}-01-29", 'name' => 'Tahun Baru Imlek'],
                ['date' => "{$year}-03-19", 'name' => 'Hari Suci Nyepi'],
                ['date' => "{$year}-03-20", 'name' => 'Hari Raya Idul Fitri'],
                ['date' => "{$year}-03-21", 'name' => 'Hari Raya Idul Fitri'],
                ['date' => "{$year}-04-03", 'name' => 'Wafat Yesus Kristus'],
                ['date' => "{$year}-05-01", 'name' => 'Hari Buruh Internasional'],
                ['date' => "{$year}-05-14", 'name' => 'Kenaikan Yesus Kristus'],
                ['date' => "{$year}-05-27", 'name' => 'Hari Raya Idul Adha'],
                ['date' => "{$year}-05-31", 'name' => 'Hari Raya Waisak'],
                ['date' => "{$year}-06-01", 'name' => 'Hari Lahir Pancasila'],
                ['date' => "{$year}-06-16", 'name' => 'Tahun Baru Islam'],
                ['date' => "{$year}-08-17", 'name' => 'Hari Kemerdekaan RI'],
                ['date' => "{$year}-09-04", 'name' => 'Maulid Nabi Muhammad SAW'],
                ['date' => "{$year}-12-25", 'name' => 'Hari Raya Natal'],
            ];
        });
    }

    /**
     * Memeriksa apakah tanggal tertentu merupakan hari libur nasional atau akhir pekan (Sabtu/Minggu).
     */
    public static function isHolidayOrWeekend(Carbon $date): bool
    {
        // Skip Sabtu dan Minggu
        if ($date->isSaturday() || $date->isSunday()) {
            return true;
        }

        $holidays = self::getIndonesianHolidays($date->year);
        $dateStr = $date->toDateString();

        foreach ($holidays as $h) {
            if (isset($h['date']) && $h['date'] === $dateStr) {
                return true;
            }
        }

        return false;
    }

    /**
     * Menghitung jumlah hari kerja efektif (Senin-Jumat, dikurangi akhir pekan dan hari libur nasional).
     */
    public static function countWorkingDays(Carbon $from, Carbon $to): int
    {
        $current = $from->copy()->startOfDay();
        $end = $to->copy()->endOfDay();
        $count = 0;

        while ($current->lte($end)) {
            if (!self::isHolidayOrWeekend($current)) {
                $count++;
            }
            $current->addDay();
        }

        return $count;
    }

    /**
     * Menjalankan proses otomatisasi Alfa & Pemotongan Quota Cuti untuk hari kerja yang sudah lewat / selesai.
     */
    public static function runAutoAlfaDeduction(): void
    {
        $today = Carbon::today();
        
        // Batas tanggal yang diproses: jika jam >= 23 malam, hari ini sudah bisa di-process.
        // Jika belum jam 23 malam, maksimal process sampai kemarin.
        $maxDate = Carbon::now()->hour >= 23 ? $today->copy() : $today->copy()->subDay();
        
        // Periksa 7 hari ke belakang untuk memastikan tidak ada hari kerja terlewat
        $startDate = $today->copy()->subDays(7);

        for ($date = $startDate; $date->lte($maxDate); $date->addDay()) {
            // 1. Skip jika hari libur nasional atau akhir pekan
            if (self::isHolidayOrWeekend($date)) {
                continue;
            }

            $dateStr = $date->toDateString();
            
            // Periksa apakah tanggal ini sudah pernah diproses auto alfa
            $alreadyRun = Configuration::where('key', 'auto_alfa_run_' . $dateStr)->exists();
            if ($alreadyRun) {
                continue;
            }

            // Jalankan dalam database transaction
            DB::transaction(function () use ($date, $dateStr) {
                Configuration::create([
                    'key'   => 'auto_alfa_run_' . $dateStr,
                    'label' => 'Tanggal Auto Alfa Dijalankan: ' . $dateStr,
                    'value' => 'done',
                    'type'  => 'text',
                    'group' => 'system',
                ]);

                // Update juga config legacy agar tetap terisi
                $config = Configuration::where('key', 'last_auto_alfa_date')->first();
                if ($config) {
                    $config->update(['value' => $dateStr]);
                } else {
                    Configuration::create([
                        'key'   => 'last_auto_alfa_date',
                        'label' => 'Tanggal Terakhir Auto Alfa Dijalankan',
                        'value' => $dateStr,
                        'type'  => 'text',
                        'group' => 'system',
                    ]);
                }
                Cache::forget("config_last_auto_alfa_date");

                // Ambil semua karyawan aktif (tetap dan kontrak)
                $activeEmployees = User::where('is_active', true)
                    ->whereHas('role', function ($q) {
                        $q->whereIn('slug', ['karyawan_tetap', 'karyawan_kontrak']);
                    })
                    ->get();

                foreach ($activeEmployees as $employee) {
                    // Cek apakah sudah ada rekaman absensi tanggal tersebut (hadir, telat, cuti, dll)
                    $hasAbsensi = Absensi::where('user_id', $employee->id)
                        ->whereDate('tanggal', $date)
                        ->exists();

                    if ($hasAbsensi) {
                        continue;
                    }

                    // Cek apakah ada perizinan yang disetujui atau masih menunggu approval pada tanggal tersebut
                    $hasLeaveApplication = DetailPerizinan::where('user_id', $employee->id)
                        ->where('status_approval', '!=', 'ditolak')
                        ->whereDate('tanggal_mulai', '<=', $date)
                        ->whereDate('tanggal_selesai', '>=', $date)
                        ->exists();

                    if ($hasLeaveApplication) {
                        continue;
                    }

                    // Resolusi mitra_id untuk kolom absensi
                    $mitraId = null;
                    if ($employee->isTetap()) {
                        $mitraPusat = Mitra::where('is_pusat', true)->first();
                        if ($mitraPusat) {
                            $mitraId = $mitraPusat->id;
                        }
                    } else {
                        $penempatan = $employee->penempatanAktif()->first();
                        if ($penempatan) {
                            $mitraId = $penempatan->mitra_id;
                        }
                    }

                    // Buat rekaman absensi ALFA
                    Absensi::create([
                        'user_id'  => $employee->id,
                        'mitra_id' => $mitraId,
                        'tanggal'  => $date,
                        'status'   => 'alfa',
                        'is_telat' => false,
                    ]);

                    // Potong kuota cuti tahunan karyawan
                    $kuota = $employee->kuotaPerizinanTahunIni();
                    if (!$kuota) {
                        $globalKuota = (int) Configuration::getValue('kuota_cuti_tahunan', 12);
                        $kuota = KuotaPerizinan::create([
                            'user_id'     => $employee->id,
                            'tahun'       => $date->year,
                            'kuota_total' => $globalKuota,
                            'terpakai'    => 0,
                            'sisa'        => $globalKuota,
                        ]);
                    }

                    if ($kuota->sisa > 0) {
                        $kuota->pakai(1);
                    }
                }
            });
        }
    }
}
