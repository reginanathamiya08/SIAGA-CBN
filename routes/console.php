<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

use Illuminate\Support\Facades\Schedule;

Artisan::command('payroll:reminder', function () {
    $namaBulan = now()->translatedFormat('F Y');
    
    // Cari periode draft untuk bulan berjalan (jika ada)
    $currentMonthStart = now()->startOfMonth()->toDateString();
    $periode = \App\Models\PeriodeGaji::where('tanggal_mulai', $currentMonthStart)->first();
    
    $link = $periode 
        ? route('admin.penggajian.show', $periode->id, false)
        : route('admin.penggajian.index', [], false);

    $admins = \App\Models\User::whereHas('role', function ($q) {
        $q->where('slug', 'admin');
    })->get();

    $count = 0;
    foreach ($admins as $admin) {
        // Hindari duplikasi notifikasi untuk periode & judul yang sama pada hari yang sama
        $exists = \App\Models\Notification::where('user_id', $admin->id)
            ->where('title', "Reminder Proses Gaji Besok! 🗓️")
            ->whereDate('created_at', now()->toDateString())
            ->exists();

        if (!$exists) {
            \App\Models\Notification::send(
                $admin->id,
                "Reminder Proses Gaji Besok! 🗓️",
                "Besok (tanggal 25) proses penggajian periode {$namaBulan} sudah mulai dapat dilakukan. Silakan periksa kembali kelengkapan data absensi dan perizinan.",
                'info',
                $link
            );
            $count++;
        }
    }
    
    $this->info("Berhasil mengirimkan {$count} notifikasi reminder ke Admin.");
})->purpose('Kirim notifikasi reminder ke Admin setiap tanggal 24');

// Jadwalkan untuk berjalan otomatis setiap tanggal 24 pukul 08:00 pagi
Schedule::command('payroll:reminder')
    ->dailyAt('08:00')
    ->when(fn() => now()->day === 24);
