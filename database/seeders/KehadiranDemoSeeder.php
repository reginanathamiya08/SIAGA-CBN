<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Absensi;
use App\Models\Mitra;
use App\Models\Shift;
use Carbon\Carbon;

class KehadiranDemoSeeder extends Seeder
{
    /**
     * Run the database seeds untuk demo Semhas.
     * Membuat data absensi lengkap untuk semua karyawan aktif
     * dengan mayoritas 'hadir', 1x 'sakit', dan 1x 'izin' per bulan.
     */
    public function run(): void
    {
        $karyawanList = User::where('is_active', true)
            ->whereHas('role', function ($q) {
                $q->whereIn('slug', ['karyawan_tetap', 'karyawan_kontrak']);
            })
            ->get();

        if ($karyawanList->isEmpty()) {
            $this->command->error('❌ Tidak ada karyawan aktif ditemukan dalam database!');
            return;
        }

        $defaultShift = Shift::first();
        $mitraPusat   = Mitra::where('is_pusat', true)->first() ?? Mitra::first();
        $mitraCabang  = Mitra::where('is_pusat', false)->first() ?? $mitraPusat;

        // Rentang waktu: Bulan Lalu & Bulan Ini
        $monthsToSeed = [
            Carbon::now()->subMonth(), // Bulan lalu
            Carbon::now(),            // Bulan ini
        ];

        $totalInserted = 0;

        foreach ($karyawanList as $karyawan) {
            // Tentukan mitra_id
            $mitraId = null;
            if ($karyawan->isTetap()) {
                $mitraId = $mitraPusat?->id;
            } else {
                $mitraId = $karyawan->penempatanAktif?->mitra_id ?? $mitraCabang?->id;
            }

            foreach ($monthsToSeed as $monthCarbon) {
                $startOfMonth = $monthCarbon->copy()->startOfMonth();
                // Sampai akhir bulan atau sampai hari ini jika bulan ini
                $endOfMonth   = $monthCarbon->isCurrentMonth() 
                    ? Carbon::today() 
                    : $monthCarbon->copy()->endOfMonth();

                // Ambil semua hari kerja (Senin - Jumat)
                $weekdays = [];
                $curr = $startOfMonth->copy();
                while ($curr->lte($endOfMonth)) {
                    if ($curr->isWeekday()) {
                        $weekdays[] = $curr->toDateString();
                    }
                    $curr->addDay();
                }

                if (empty($weekdays)) {
                    continue;
                }

                // Acak 1 hari untuk Sakit dan 1 hari untuk Izin jika jumlah hari kerja cukup
                shuffle($weekdays);
                $sakitDate = count($weekdays) > 2 ? array_pop($weekdays) : null;
                $izinDate  = count($weekdays) > 2 ? array_pop($weekdays) : null;
                $telatDate = count($weekdays) > 3 ? array_pop($weekdays) : null;

                // Kembalikan sisa hari kerja sebagai 'hadir'
                // Re-sort hari kerja agar teratur berdasarkan tanggal
                $allDates = array_merge(
                    $weekdays,
                    array_filter([$sakitDate, $izinDate, $telatDate])
                );
                sort($allDates);

                foreach ($allDates as $dateStr) {
                    $tanggal = Carbon::parse($dateStr);
                    $status  = 'hadir';
                    $isTelat = false;
                    $waktuMasuk  = null;
                    $waktuPulang = null;

                    if ($dateStr === $sakitDate) {
                        $status = 'sakit';
                    } elseif ($dateStr === $izinDate) {
                        $status = 'izin';
                    } elseif ($dateStr === $telatDate) {
                        $status  = 'hadir';
                        $isTelat = true;
                        // Jam masuk telat: 08:15 - 08:35
                        $randMinute  = rand(15, 35);
                        $waktuMasuk  = $tanggal->copy()->setTime(8, $randMinute, rand(0, 59));
                        $waktuPulang = $tanggal->copy()->setTime(16, rand(5, 30), rand(0, 59));
                    } else {
                        // Hadir Tepat Waktu: 07:40 - 07:55
                        $randMinute  = rand(40, 55);
                        $waktuMasuk  = $tanggal->copy()->setTime(7, $randMinute, rand(0, 59));
                        $waktuPulang = $tanggal->copy()->setTime(16, rand(0, 25), rand(0, 59));
                    }

                    Absensi::updateOrCreate(
                        [
                            'user_id' => $karyawan->id,
                            'tanggal' => $dateStr,
                        ],
                        [
                            'mitra_id'     => $mitraId,
                            'shift_id'     => $defaultShift?->id,
                            'waktu_masuk'  => $waktuMasuk,
                            'waktu_pulang' => $waktuPulang,
                            'lat_masuk'    => $status === 'sakit' || $status === 'izin' ? null : -0.947000,
                            'long_masuk'   => $status === 'sakit' || $status === 'izin' ? null : 100.363000,
                            'ip_masuk'     => $status === 'sakit' || $status === 'izin' ? null : '127.0.0.1',
                            'lat_pulang'   => $status === 'sakit' || $status === 'izin' ? null : -0.947000,
                            'long_pulang'  => $status === 'sakit' || $status === 'izin' ? null : 100.363000,
                            'ip_pulang'    => $status === 'sakit' || $status === 'izin' ? null : '127.0.0.1',
                            'status'       => $status,
                            'is_telat'     => $isTelat,
                        ]
                    );

                    $totalInserted++;
                }
            }
        }

        $this->command->info("✅ Berhasil men-generate {$totalInserted} data kehadiran demo!");
        $this->command->info("📊 Setiap karyawan sekarang memiliki kehadiran penuh (Mayoritas Hadir, 1x Sakit, 1x Izin).");
    }
}
