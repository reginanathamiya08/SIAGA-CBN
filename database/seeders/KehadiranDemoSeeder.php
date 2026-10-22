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
     * Generate data absensi demo untuk April - Juli 2026.
     * Absensi dimulai dari tanggal_masuk masing-masing karyawan.
     * Setiap bulan: mayoritas hadir, 1-2x sakit, 1-2x izin, 1-2x telat.
     */
    public function run(): void
    {
        $karyawanList = User::where('is_active', true)
            ->whereHas('role', function ($q) {
                $q->whereIn('slug', ['karyawan_tetap', 'karyawan_kontrak']);
            })
            ->get();

        if ($karyawanList->isEmpty()) {
            $this->command->error('❌ Tidak ada karyawan aktif ditemukan!');
            return;
        }

        $defaultShift = Shift::first();
        $mitraPusat   = Mitra::where('is_pusat', true)->first() ?? Mitra::first();
        $mitraCabang  = Mitra::where('is_pusat', false)->first() ?? $mitraPusat;

        // Rentang bulan yang akan di-seed: April - Juli 2026
        $monthsToSeed = [
            Carbon::create(2026, 4, 1), // April
            Carbon::create(2026, 5, 1), // Mei
            Carbon::create(2026, 6, 1), // Juni
            Carbon::create(2026, 7, 1), // Juli
        ];

        $totalInserted = 0;
        $totalSkipped  = 0;

        foreach ($karyawanList as $karyawan) {
            // Tanggal masuk karyawan — absensi tidak boleh sebelum ini
            $tanggalMasuk = $karyawan->tanggal_masuk
                ? Carbon::parse($karyawan->tanggal_masuk)
                : Carbon::create(2026, 1, 1);

            // Tentukan mitra_id
            $mitraId = null;
            if ($karyawan->isTetap()) {
                $mitraId = $mitraPusat?->id;
            } else {
                $mitraId = $karyawan->penempatanAktif?->mitra_id ?? $mitraCabang?->id;
            }

            foreach ($monthsToSeed as $monthCarbon) {
                $startOfMonth = $monthCarbon->copy()->startOfMonth();
                $endOfMonth   = $monthCarbon->copy()->endOfMonth();

                // Batas awal absensi = mana yang lebih baru antara
                // awal bulan atau tanggal_masuk karyawan
                $startDate = $startOfMonth->lt($tanggalMasuk)
                    ? $tanggalMasuk->copy()
                    : $startOfMonth->copy();

                // Kalau karyawan belum masuk di bulan ini, skip
                if ($startDate->gt($endOfMonth)) {
                    $totalSkipped++;
                    continue;
                }

                // Kumpulkan semua hari kerja (Senin - Jumat) di rentang ini
                $weekdays = [];
                $curr = $startDate->copy();
                while ($curr->lte($endOfMonth)) {
                    if ($curr->isWeekday()) {
                        $weekdays[] = $curr->toDateString();
                    }
                    $curr->addDay();
                }

                if (empty($weekdays)) {
                    continue;
                }

                // Acak: 1-2 hari sakit, 1-2 hari izin, 1-2 hari telat per bulan
                shuffle($weekdays);
                $jumlahSakit = count($weekdays) >= 10 ? rand(1, 2) : (count($weekdays) >= 5 ? 1 : 0);
                $jumlahIzin  = count($weekdays) >= 10 ? rand(1, 2) : (count($weekdays) >= 5 ? 1 : 0);
                $jumlahTelat = count($weekdays) >= 8  ? rand(1, 2) : (count($weekdays) >= 4 ? 1 : 0);

                $sakitDates = array_splice($weekdays, 0, $jumlahSakit);
                $izinDates  = array_splice($weekdays, 0, $jumlahIzin);
                $telatDates = array_splice($weekdays, 0, $jumlahTelat);
                // Sisa = hadir

                $allDates = array_merge($weekdays, $sakitDates, $izinDates, $telatDates);
                sort($allDates);

                foreach ($allDates as $dateStr) {
                    $tanggal    = Carbon::parse($dateStr);
                    $isTelat    = false;
                    $waktuMasuk = null;
                    $waktuPulang= null;

                    if (in_array($dateStr, $sakitDates)) {
                        $status = 'alfa'; // alfa mewakili tidak hadir (sakit)
                    } elseif (in_array($dateStr, $izinDates)) {
                        $status = 'alfa'; // alfa mewakili tidak hadir (izin)
                    } elseif (in_array($dateStr, $telatDates)) {
                        $status      = 'hadir'; // tetap hadir tapi telat
                        $isTelat     = true;
                        $randMenit   = rand(15, 45);
                        $waktuMasuk  = $tanggal->copy()->setTime(8, $randMenit, rand(0, 59));
                        $waktuPulang = $tanggal->copy()->setTime(16, rand(30, 59), rand(0, 59));
                    } else {
                        // Hadir tepat waktu: 07:40 - 07:59
                        $status      = 'hadir';
                        $randMenit   = rand(40, 59);
                        $waktuMasuk  = $tanggal->copy()->setTime(7, $randMenit, rand(0, 59));
                        $waktuPulang = $tanggal->copy()->setTime(16, rand(0, 30), rand(0, 59));
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
                            'lat_masuk'    => $waktuMasuk ? -0.947000 : null,
                            'long_masuk'   => $waktuMasuk ? 100.363000 : null,
                            'ip_masuk'     => $waktuMasuk ? '127.0.0.1' : null,
                            'lat_pulang'   => $waktuPulang ? -0.947000 : null,
                            'long_pulang'  => $waktuPulang ? 100.363000 : null,
                            'ip_pulang'    => $waktuPulang ? '127.0.0.1' : null,
                            'status'       => $status,
                            'is_telat'     => $isTelat,
                        ]
                    );

                    $totalInserted++;
                }
            }
        }

        $this->command->info("✅ Berhasil generate {$totalInserted} data absensi demo (April - Juli 2026)!");
        $this->command->info("⏭️  {$totalSkipped} bulan dilewati karena karyawan belum masuk.");
        $this->command->info("📊 Status: Mayoritas Hadir, sebagian Telat, sebagian Alfa (Sakit/Izin).");
    }
}


