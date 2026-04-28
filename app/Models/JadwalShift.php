<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JadwalShift extends Model
{
    protected $table = 'jadwal_shift';

    protected $fillable = [
        'karyawan_id',
        'shift_id',
        'tanggal',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    // ── Relasi ──────────────────────────────────────────────────────────

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class);
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }
}