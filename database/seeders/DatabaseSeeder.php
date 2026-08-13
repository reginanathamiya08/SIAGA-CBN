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
        // ── 0. DATA ROLES ────────────────────────────────────────────────
        $roles = [
            ['nama_role' => 'Administrator',     'slug' => 'admin'],
            ['nama_role' => 'Pimpinan',          'slug' => 'pimpinan'],
            ['nama_role' => 'Karyawan Tetap',    'slug' => 'karyawan_tetap'],
            ['nama_role' => 'Karyawan Kontrak', 'slug' => 'karyawan_kontrak'],
        ];

        foreach ($roles as $r) {
            \App\Models\Role::create($r);
        }

        // ── 1. AKUN ADMIN ────────────────────────────────────────────────
        $roleAdmin = \App\Models\Role::where('slug', 'admin')->first();
        User::create([
            'role_id'        => $roleAdmin->id,
            'nip'            => 'ADM-CBN-0001',
            'password'       => Hash::make('admin123'),
            'nama'           => 'Administrator Utama',
            'divisi'         => 'adm_umum',
            'jabatan'        => 'Staff Administrasi & Umum',
            'tanggal_masuk'  => now(),
            'is_active'      => true,
        ]);

        // ── 2. AKUN PIMPINAN ─────────────────────────────────────────────
        $rolePimpinan = \App\Models\Role::where('slug', 'pimpinan')->first();
        User::create([
            'role_id'        => $rolePimpinan->id,
            'nip'            => 'PM-CBN-0001',
            'password'       => Hash::make('pimpinan123'),
            'nama'           => 'Pimpinan PT CBN',
            'divisi'         => 'manajemen',
            'jabatan'        => 'Direktur Utama',
            'tanggal_masuk'  => now(),
            'is_active'      => true,
        ]);

        // ── 3. DATA SHIFT ─────────────────────────────────────────────────
        // Shift default untuk satpam, card center, monitoring ATM & jaringan.
        // Admin bisa menambah/mengubah shift lewat sistem.
        $shifts = [
            [
                'nama_shift'  => 'Pagi',
                'jam_mulai'   => '06:00:00',
                'jam_selesai' => '14:00:00',
            ],
            [
                'nama_shift'  => 'Siang',
                'jam_mulai'   => '14:00:00',
                'jam_selesai' => '22:00:00',
            ],
            [
                'nama_shift'  => 'Malam',
                'jam_mulai'   => '22:00:00',
                'jam_selesai' => '06:00:00',
            ],
        ];

        foreach ($shifts as $shift) {
            Shift::create($shift);
        }

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