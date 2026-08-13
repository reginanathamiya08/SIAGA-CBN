<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasCustomId;

class DetailRiwayatPenempatan extends Model
{
    use HasCustomId;

    const ID_PREFIX = 'PNP';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $table = 'detail_riwayat_penempatan';

    protected $fillable = [
        'user_id',
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
        return $this->belongsTo(User::class, 'user_id');
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
