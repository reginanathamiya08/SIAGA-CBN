<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    protected $table = 'shift';

    protected $fillable = [
        'nama_shift',
        'jam_masuk',
        'jam_keluar',
        'batas_telat',
    ];

    // ── Relasi ──────────────────────────────────────────────────────────

    public function jadwal()
    {
        return $this->hasMany(JadwalShift::class);
    }
}