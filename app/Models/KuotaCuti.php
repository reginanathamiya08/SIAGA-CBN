<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KuotaCuti extends Model
{
    protected $table = 'kuota_cuti';

    protected $fillable = [
        'karyawan_id',
        'tahun',
        'kuota_total',
        'terpakai',
        'sisa',
    ];

    // ── Relasi ──────────────────────────────────────────────────────────

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class);
    }

    // ── Helper ──────────────────────────────────────────────────────────

    /**
     * Pakai sejumlah hari cuti.
     * Dipanggil saat pengajuan cuti/izin disetujui.
     */
    public function pakai(int $hari): void
    {
        $this->increment('terpakai', $hari);
        $this->decrement('sisa', $hari);
    }

    /**
     * Kembalikan cuti yang sudah dipakai.
     * Dipanggil saat pengajuan ditolak atau dibatalkan.
     */
    public function kembalikan(int $hari): void
    {
        $this->terpakai = max(0, $this->terpakai - $hari);
        $this->sisa     = min($this->kuota_total, $this->sisa + $hari);
        $this->save();
    }

    /**
     * Cek apakah masih ada sisa kuota cuti.
     */
    public function masihAda(int $hari = 1): bool
    {
        return $this->sisa >= $hari;
    }
}