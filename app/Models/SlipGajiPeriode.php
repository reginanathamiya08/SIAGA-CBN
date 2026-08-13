<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasCustomId;

class SlipGajiPeriode extends Model
{
    use HasCustomId;

    const ID_PREFIX = 'SLP';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $table = 'slip_gaji_periode';

    protected $fillable = [
        'user_id',
        'periode_id',
        'total_hadir',
        'total_telat',
        'total_alfa',
        'total_izin',
        'total_cuti',
        'total_potongan',
        'gaji_bersih',
        'status',
        'alasan_tolak',
        'diterbitkan_at',
    ];

    protected $casts = [
        'total_hadir'    => 'integer',
        'total_telat'    => 'integer',
        'total_alfa'     => 'integer',
        'total_izin'     => 'integer',
        'total_cuti'     => 'integer',
        'total_potongan' => 'float',
        'gaji_bersih'    => 'float',
        'diterbitkan_at' => 'datetime',
    ];

    // ── Relasi ──────────────────────────────────────────────────────

    public function karyawan()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function periodeGaji()
    {
        return $this->belongsTo(PeriodeGaji::class, 'periode_id');
    }

    public function periode()
    {
        return $this->belongsTo(PeriodeGaji::class, 'periode_id');
    }

    public function perizinan()
    {
        return $this->hasMany(DetailPerizinan::class, 'slip_gaji_periode_id');
    }

    public function details()
    {
        return $this->hasMany(DetailGajiKomponen::class, 'slip_gaji_periode_id');
    }

    // ── Helper ──────────────────────────────────────────────────────

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isDiterbitkan(): bool
    {
        return $this->status === 'diterbitkan';
    }

    public function totalPendapatan(): float
    {
        return (float) $this->details->filter(function($detail) {
            return $detail->tipe === 'pendapatan';
        })->sum('nominal');
    }

    public function getNominal(string $namaKomponen): float
    {
        $detail = $this->details->first(function($d) use ($namaKomponen) {
            return ($d->nama_komponen ?? '') === $namaKomponen;
        });
        return $detail ? (float) $detail->nominal : 0.0;
    }

    public function getPendapatanLainnya(): float
    {
        return $this->getNominal('Pendapatan Lainnya');
    }

    public function formatRp(float $angka): string
    {
        return 'Rp ' . number_format($angka, 0, ',', '.');
    }
}
