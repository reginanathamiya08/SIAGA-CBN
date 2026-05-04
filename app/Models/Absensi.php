<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Absensi extends Model
{
    protected $table = 'absensi';

    protected $fillable = [
        'karyawan_id',
        'mitra_id',
        'shift_id',
        'tanggal',
        'waktu_masuk',
        'waktu_pulang',
        'lat_masuk',
        'long_masuk',
        'ip_masuk',
        'lat_pulang',
        'long_pulang',
        'ip_pulang',
        'status',
        'is_telat',
    ];

    protected $casts = [
        'tanggal'      => 'date',
        'waktu_masuk'  => 'datetime',
        'waktu_pulang' => 'datetime',
        'is_telat'     => 'boolean',
        'lat_masuk'    => 'float',
        'long_masuk'   => 'float',
        'lat_pulang'   => 'float',
        'long_pulang'  => 'float',
    ];

    // ── Relasi ──────────────────────────────────────────────────────────

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class);
    }

    public function mitra()
    {
        return $this->belongsTo(Mitra::class);
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    // ── Helper ──────────────────────────────────────────────────────────

    public function sudahPulang(): bool
    {
        return !is_null($this->waktu_pulang);
    }

    /**
     * Hitung durasi kerja dalam menit.
     */
    public function durasiMenit(): ?int
    {
        if (!$this->waktu_masuk || !$this->waktu_pulang) {
            return null;
        }

        return (int) $this->waktu_masuk->diffInMinutes($this->waktu_pulang);
    }
}