<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Penempatan extends Model
{
    protected $table = 'penempatan';

    protected $fillable = [
        'karyawan_id',
        'mitra_id',
        'tanggal_mulai',
        'tanggal_selesai',
        'status',
    ];

    protected $casts = [
        'tanggal_mulai'   => 'date',
        'tanggal_selesai' => 'date',
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

    // ── Helper ──────────────────────────────────────────────────────────

    public function isAktif(): bool
    {
        return $this->status === 'aktif';
    }
}