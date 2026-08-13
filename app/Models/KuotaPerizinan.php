<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasCustomId;

class KuotaPerizinan extends Model
{
    use HasCustomId;

    const ID_PREFIX = 'CUT';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $table = 'kuota_perizinan';

    protected $fillable = [
        'user_id',
        'tahun',
        'kuota_total',
        'terpakai',
        'sisa',
    ];

    // ── Relasi ──────────────────────────────────────────────────────────

    public function karyawan()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function detailPerizinan()
    {
        return $this->hasMany(DetailPerizinan::class, 'kuota_perizinan_id');
    }

    // ── Helper ──────────────────────────────────────────────────────────

    /**
     * Pakai sejumlah hari cuti/perizinan.
     * Dipanggil saat pengajuan cuti/izin disetujui.
     */
    public function pakai(int $hari): void
    {
        $this->increment('terpakai', $hari);
        $this->decrement('sisa', $hari);
    }

    /**
     * Kembalikan cuti/perizinan yang sudah dipakai.
     * Dipanggil saat pengajuan ditolak atau dibatalkan.
     */
    public function kembalikan(int $hari): void
    {
        $this->terpakai = max(0, $this->terpakai - $hari);
        $this->sisa     = min($this->kuota_total, $this->sisa + $hari);
        $this->save();
    }

    /**
     * Cek apakah masih ada sisa kuota.
     */
    public function masihAda(int $hari = 1): bool
    {
        return $this->sisa >= $hari;
    }
}
