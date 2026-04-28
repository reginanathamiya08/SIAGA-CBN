<?php

namespace App\Services;

use App\Models\User;

class UsernameGeneratorService
{
    /**
     * ─────────────────────────────────────────────────────────────────────
     * FORMAT USERNAME:
     *   Admin                        → ADM-CBN-0001
     *   Pimpinan                     → PM-CBN-0001
     *   Karyawan Tetap               → KT-CBN-0001
     *   Karyawan Kontrak Divisi HC   → KK-HC-0001
     *   Karyawan Kontrak Divisi Umum → KK-UM-0001
     * ─────────────────────────────────────────────────────────────────────
     */

    /**
     * Generate username baru berdasarkan role dan divisi.
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
        $last = User::where('username', 'LIKE', $prefix . '-%')
                    ->orderBy('username', 'desc')
                    ->value('username');

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
     * Daftar divisi berdasarkan jenis karyawan.
     * Return: array ['value' => 'label']
     */
    public static function daftarDivisi(string $jenisKaryawan): array
    {
        if ($jenisKaryawan === 'tetap') {
            return [
                'keuangan'       => 'Keuangan',
                'koordinator_cs' => 'Koordinator CS',
                'adm_umum'       => 'Administrasi & Umum',
            ];
        }

        if ($jenisKaryawan === 'kontrak') {
            return [
                'HC'   => 'HC (Human Capital)',
                'umum' => 'Umum',
            ];
        }

        return [];
    }

    /**
     * Daftar jabatan berdasarkan jenis karyawan dan divisi.
     * Return: array ['value' => 'label'] atau array string
     *
     * KARYAWAN TETAP:
     *   - Keuangan       : Staff Keuangan
     *   - Koordinator CS : Koordinator CS
     *   - Adm & Umum     : Staff Administrasi & Umum
     *
     * KARYAWAN KONTRAK DIVISI HC:
     *   Satpam, Sopir, Marketing, Pramusaji, Pramubakti,
     *   Call Centre, Card Center, E-Channel, Juru Parkir,
     *   Teknisi, Monitoring ATM dan Jaringan, PPI
     *   → Catatan:
     *     - Satpam   : wajib KTA, bersifat shift
     *     - Card Center          : bersifat shift
     *     - Monitoring ATM & Jar.: bersifat shift
     *     - Marketing, Call Centre, Card Center,
     *       Teknisi, Monitoring ATM, PPI : gaji di atas UMR
     *     - Lainnya  : UMR tahun berjalan
     *     - Uang makan dibayar oleh mitra (bukan CBN)
     *     - Sopir    : wajib SIM
     *     - Pramubakti: wajib pengalaman
     *
     * KARYAWAN KONTRAK DIVISI UMUM:
     *   CS, CS ATM, Ekspedisi
     *   → Catatan:
     *     - Gaji UMR tahun berjalan
     *     - Uang makan dibayar oleh CBN (35.000/hari)
     *     - CS: absen 1 jam sebelum jam operasional perusahaan mitra
     */
    public static function daftarJabatan(string $jenisKaryawan, ?string $divisi = null): array
    {
        if ($jenisKaryawan === 'tetap') {
            return match ($divisi) {
                'keuangan'       => ['Staff Keuangan'],
                'koordinator_cs' => ['Koordinator CS'],
                'adm_umum'       => ['Staff Administrasi & Umum'],
                default          => [],
            };
        }

        if ($jenisKaryawan === 'kontrak') {
            return match (strtolower($divisi ?? '')) {
                'hc' => [
                    'Satpam',
                    'Sopir',
                    'Marketing',
                    'Pramusaji',
                    'Pramubakti',
                    'Call Centre',
                    'Card Center',
                    'E-Channel',
                    'Juru Parkir',
                    'Teknisi',
                    'Monitoring ATM dan Jaringan',
                    'PPI',
                ],
                'umum' => [
                    'CS',
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
     * (Satpam, Card Center, Monitoring ATM dan Jaringan)
     */
    public static function jabatanShift(): array
    {
        return [
            'Satpam',
            'Card Center',
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
            'Card Center',
            'Teknisi',
            'Monitoring ATM dan Jaringan',
            'PPI',
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
     * Apakah CS (divisi Umum) perlu absen lebih awal?
     * CS absen 1 jam sebelum jam operasional perusahaan mitra.
     */
    public static function isAbsenLebihAwal(string $jabatan): bool
    {
        return in_array($jabatan, ['CS', 'CS ATM']);
    }
}