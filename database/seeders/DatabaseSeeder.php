<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Shift;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── 1. AKUN ADMIN ────────────────────────────────────────────────
        // Username mengikuti format: ADM-CBN-0001
        // Admin bertugas membuat akun karyawan lewat sistem.
        User::create([
            'username'  => 'ADM-CBN-0001',
            'password'  => Hash::make('admin123'),
            'role'      => 'admin',
            'is_active' => true,
        ]);

        // ── 2. AKUN PIMPINAN ─────────────────────────────────────────────
        // Username mengikuti format: PM-CBN-0001
        User::create([
            'username'  => 'PM-CBN-0001',
            'password'  => Hash::make('pimpinan123'),
            'role'      => 'pimpinan',
            'is_active' => true,
        ]);

        // ── 3. DATA SHIFT ─────────────────────────────────────────────────
        // Shift default untuk satpam, card center, monitoring ATM & jaringan.
        // Admin bisa menambah/mengubah shift lewat sistem.
        Shift::insert([
            [
                'nama_shift'  => 'Pagi',
                'jam_masuk'   => '06:00:00',
                'jam_keluar'  => '14:00:00',
                'batas_telat' => '06:15:00',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'nama_shift'  => 'Siang',
                'jam_masuk'   => '14:00:00',
                'jam_keluar'  => '22:00:00',
                'batas_telat' => '14:15:00',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'nama_shift'  => 'Malam',
                'jam_masuk'   => '22:00:00',
                'jam_keluar'  => '06:00:00',
                'batas_telat' => '22:15:00',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
        ]);

        // ── RINGKASAN ─────────────────────────────────────────────────────
        $this->command->info('');
        $this->command->info('✅ Seeder berhasil! Akun yang tersedia:');
        $this->command->table(
            ['Username', 'Password', 'Role'],
            [
                ['ADM-CBN-0001', 'admin123',    'Admin'],
                ['PM-CBN-0001',  'pimpinan123', 'Pimpinan'],
            ]
        );
        $this->command->info('');
        $this->command->info('ℹ️  Format username karyawan:');
        $this->command->table(
            ['Role', 'Format', 'Contoh'],
            [
                ['Karyawan Tetap',          'KT-CBN-XXXX', 'KT-CBN-0001'],
                ['Karyawan Kontrak Div. HC', 'KK-HC-XXXX',  'KK-HC-0001'],
                ['Karyawan Kontrak Div. Umum', 'KK-UM-XXXX', 'KK-UM-0001'],
            ]
        );
        $this->command->info('');
        $this->command->info('ℹ️  Username karyawan di-generate otomatis oleh sistem saat admin tambah karyawan.');
        $this->command->info('⚠️  Ganti password default sebelum deploy ke production!');
    }
}