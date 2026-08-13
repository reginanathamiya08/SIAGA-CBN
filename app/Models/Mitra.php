<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasCustomId;

class Mitra extends Model
{
    use HasCustomId;

    const ID_PREFIX = 'MTR';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $table = 'mitra';

    protected $fillable = [
        'nama_mitra',
        'latitude',
        'longitude',
        'radius_meter',
        'ip_public',
        'mitra_induk_id',
        'is_cabang',
        'jam_masuk',
        'jam_pulang',
        'is_pusat',
    ];

    protected $casts = [
        'latitude'  => 'float',
        'longitude' => 'float',
        'is_cabang' => 'boolean',
        'is_pusat'  => 'boolean',
    ];

    // ── Relasi self-referencing (induk ↔ cabang) ─────────────────────────

    public function induk()
    {
        return $this->belongsTo(Mitra::class, 'mitra_induk_id');
    }

    public function cabang()
    {
        return $this->hasMany(Mitra::class, 'mitra_induk_id');
    }

    // ── Relasi ke tabel lain ─────────────────────────────────────────────

    public function penempatan()
    {
        return $this->hasMany(DetailRiwayatPenempatan::class);
    }

    public function absensi()
    {
        return $this->hasMany(Absensi::class);
    }

    public function shifts()
    {
        return $this->hasMany(Shift::class);
    }

    // ── Helper ──────────────────────────────────────────────────────────

    public function isInduk(): bool
    {
        return !$this->is_cabang;
    }
}
