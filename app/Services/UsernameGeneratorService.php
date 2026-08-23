<?php

namespace App\Services;

use App\Models\User;

class UsernameGeneratorService
{
    /**
     * ─────────────────────────────────────────────────────────────────────
     * FORMAT NIP:
     *   Admin                        → ADM-CBN-0001
     *   Pimpinan                     → PM-CBN-0001
     *   Karyawan Tetap               → KT-CBN-0001
     *   Karyawan Kontrak Divisi HC   → KK-HC-0001
     *   Karyawan Kontrak Divisi Umum → KK-UM-0001
     * ─────────────────────────────────────────────────────────────────────
     */

    /**
     * Generate NIP baru berdasarkan role dan divisi.
     */
    public function generate(string $role, ?string $divisi = null): string
    {
        $prefix = $this->getPrefix($role, $divisi);
        $nomor  = $this->getNextNomor($prefix);

        return $prefix . '-' . str_pad($nomor, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Tentukan prefix berdasarkan role dan divisi.
     */
    private function getPrefix(string $role, ?string $divisi): string
    {
        return match ($role) {
            'admin'            => 'ADM-CBN',
            'pimpinan'         => 'PM-CBN',
            'karyawan_tetap'   => 'KT-CBN',
            'karyawan_kontrak' => match (strtolower($divisi ?? '')) {
                'hc'   => 'KK-HC',
                'umum' => 'KK-UM',
                default => throw new \InvalidArgumentException(
                    "Divisi '{$divisi}' tidak valid untuk karyawan kontrak."
                ),
            },
            default => throw new \InvalidArgumentException(
                "Role '{$role}' tidak dikenali."
            ),
        };
    }

    /**
     * Cari nomor urut berikutnya untuk prefix tertentu.
     */
    private function getNextNomor(string $prefix): int
    {
        $last = User::where('nip', 'LIKE', $prefix . '-%')
                    ->orderBy('nip', 'desc')
                    ->value('nip');

        if (!$last) {
            return 1;
        }

        $parts = explode('-', $last);
        $nomor = (int) end($parts);

        return $nomor + 1;
    }

    // ─────────────────────────────────────────────────────────────────────
    // DATA DIVISI & JABATAN PT CBN
    // ─────────────────────────────────────────────────────────────────────

    /**
     * Resolve role parameter to role slug ('karyawan_tetap' or 'karyawan_kontrak').
     */
    private static function resolveRoleSlug(int|string|\App\Models\Role $role): string
    {
        if ($role instanceof \App\Models\Role) {
            return $role->slug;
        }

        $str = (string) $role;

        if (in_array($str, ['karyawan_tetap', 'tetap', 'JNS-00001'])) {
            return 'karyawan_tetap';
        }

        if (in_array($str, ['karyawan_kontrak', 'kontrak', 'JNS-00002'])) {
            return 'karyawan_kontrak';
        }

        $found = \App\Models\Role::find($str);
        if ($found) {
            return $found->slug;
        }

        return $str;
    }

    /**
     * Daftar divisi berdasarkan role karyawan.
     * Return: array ['value' => 'label']
     */
    public static function daftarDivisi(int|string|\App\Models\Role $role): array
    {
        $slug = self::resolveRoleSlug($role);

        if ($slug === 'karyawan_tetap') {
            return [
                'keuangan'       => 'Keuangan',
                'koordinator_cs' => 'Koordinator CS',
                'adm_umum'       => 'Administrasi & Umum',
                'manajemen'      => 'Manajemen / Direksi',
            ];
        }

        if ($slug === 'karyawan_kontrak') {
            return [
                'HC'   => 'HC (Human Capital)',
                'umum' => 'Umum',
            ];
        }

        return [];
    }

    /**
     * Daftar jabatan berdasarkan role karyawan dan divisi.
     * Return: array ['value' => 'label'] atau array string
     */
    public static function daftarJabatan(int|string|\App\Models\Role $role, ?string $divisi = null): array
    {
        $slug = self::resolveRoleSlug($role);

        if ($slug === 'karyawan_tetap') {
            return match ($divisi) {
                'keuangan'       => ['Kepala Divisi Keuangan', 'Staff Keuangan'],
                'koordinator_cs' => ['Kepala Divisi CS', 'Staff CS'],
                'adm_umum'       => ['Kepala Divisi Administrasi & Umum', 'Staff Administrasi & Umum'],
                'manajemen'      => ['Direktur Utama'],
                default          => [],
            };
        }

        if ($slug === 'karyawan_kontrak') {
            return match (strtolower($divisi ?? '')) {
                'hc' => [
                    'Satpam',
                    'Sopir',
                    'Marketing',
                    'Pramusaji',
                    'Pramubakti',
                    'Call Centre',
                    'Card Centre',
                    'E-Channel',
                    'Juru Parkir',
                    'Teknisi',
                    'Monitoring ATM dan Jaringan',
                    'PPID',
                ],
                'umum' => [
                    'Cleaning Service',
                    'CS ATM',
                    'Ekspedisi',
                ],
                default => [],
            };
        }

        return [];
    }

    /**
     * Jabatan yang bersifat shift.
     * (Satpam, Card Center / Card Centre, Monitoring ATM dan Jaringan)
     */
    public static function jabatanShift(): array
    {
        return [
            'Satpam',
            'Monitoring ATM dan Jaringan',
        ];
    }

    /**
     * Jabatan yang gajinya di atas UMR (divisi HC).
     */
    public static function jabatanAtasUmr(): array
    {
        return [
            'Marketing',
            'Call Centre',
            'Call Center',
            'Card Center',
            'Card Centre',
            'Teknisi',
            'Monitoring ATM dan Jaringan',
            'PPI',
            'PPID',
        ];
    }

    /**
     * Jabatan yang memerlukan dokumen khusus saat pendaftaran.
     * Return: ['jabatan' => ['jenis' => '...', 'keterangan' => '...']]
     */
    public static function jabatanDenganDokumenKhusus(): array
    {
        return [
            'Satpam' => [
                'jenis'      => 'KTA',
                'keterangan' => 'Satpam wajib memiliki KTA (Kartu Tanda Anggota) yang masih berlaku.',
            ],
            'Sopir' => [
                'jenis'      => 'SIM',
                'keterangan' => 'Sopir wajib memiliki SIM (Surat Izin Mengemudi) yang masih berlaku.',
            ],
        ];
    }

    /**
     * Apakah jabatan ini bersifat shift?
     */
    public static function isShift(string $jabatan): bool
    {
        return in_array($jabatan, self::jabatanShift());
    }

    /**
     * Apakah jabatan ini gajinya di atas UMR?
     */
    public static function isAtasUmr(string $jabatan): bool
    {
        return in_array($jabatan, self::jabatanAtasUmr());
    }

    /**
     * Apakah uang makan karyawan kontrak dibayar oleh mitra (bukan CBN)?
     * Divisi HC  : dibayar mitra  → true
     * Divisi Umum: dibayar CBN   → false
     */
    public static function uangMakanDibayarMitra(?string $divisi): bool
    {
        return strtolower($divisi ?? '') === 'hc';
    }

    /**
     * Apakah CS / Cleaning Service (divisi Umum) perlu absen lebih awal?
     * CS absen 1 jam sebelum jam operasional perusahaan mitra.
     */
    public static function isAbsenLebihAwal(string $jabatan): bool
    {
        return in_array($jabatan, ['CS', 'CS ATM', 'Cleaning Service']);
    }
}
