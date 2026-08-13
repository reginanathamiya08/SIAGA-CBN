<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasCustomId;

class PeriodeGaji extends Model
{
    use HasCustomId;

    const ID_PREFIX = 'PRD';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $table = 'periode_gaji';

    protected $fillable = [
        'nama_periode',
        'tanggal_mulai',
        'tanggal_selesai',
        'status',
        'finalisasi_at',
        'finalisasi_by',
    ];

    protected $casts = [
        'tanggal_mulai'   => 'date',
        'tanggal_selesai' => 'date',
        'finalisasi_at'   => 'datetime',
    ];

    // ── Relasi ──────────────────────────────────────────────────────

    public function finalizer()
    {
        return $this->belongsTo(User::class, 'finalisasi_by');
    }

    public function slipGaji()
    {
        // FK di slip_gaji adalah periode_id (bukan periode_gaji_id)
        return $this->hasMany(SlipGajiPeriode::class, 'periode_id');
    }

    // ── Helper ──────────────────────────────────────────────────────

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isProses(): bool
    {
        return $this->status === 'proses';
    }

    public function isFinal(): bool
    {
        return $this->status === 'final';
    }

    public function labelStatus(): string
    {
        return match ($this->status) {
            'draft'  => 'Draft',
            'proses' => 'Menunggu Persetujuan',
            'final'  => 'Final',
            default  => $this->status,
        };
    }

    public function totalPengeluaran(): float
    {
        return (float) $this->slipGaji()->sum('gaji_bersih');
    }

    public function totalPotongan(): float
    {
        return (float) $this->slipGaji()->sum('total_potongan');
    }
}
