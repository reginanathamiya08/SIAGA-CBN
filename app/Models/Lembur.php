<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lembur extends Model
{
    protected $table = 'lembur';

    protected $fillable = [
        'karyawan_id',
        'tanggal',
        'jam_mulai',
        'jam_selesai',
        'total_jam',
        'keterangan',
        'status_approval',
        'approved_by',
        'approved_at',
        'alasan_tolak',
    ];

    protected $casts = [
        'tanggal'     => 'date',
        'approved_at' => 'datetime',
        'total_jam'   => 'float',
    ];

    // ── Relasi ──────────────────────────────────────────────────────────

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // ── Helper ──────────────────────────────────────────────────────────

    public function isMenunggu(): bool
    {
        return $this->status_approval === 'menunggu';
    }

    public function isDisetujui(): bool
    {
        return $this->status_approval === 'disetujui';
    }

    public function isDitolak(): bool
    {
        return $this->status_approval === 'ditolak';
    }

    /**
     * Format total jam lembur menjadi "Xj Ym".
     */
    public function formatDurasi(): string
    {
        $jam   = floor($this->total_jam);
        $menit = round(($this->total_jam - $jam) * 60);

        return "{$jam}j {$menit}m";
    }
}