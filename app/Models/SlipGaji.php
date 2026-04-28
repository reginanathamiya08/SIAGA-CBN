<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SlipGaji extends Model
{
    protected $table = 'slip_gaji';

    protected $fillable = [
        'karyawan_id',
        'periode_id',           // FK ke periode_gaji.id
        'gaji_pokok',
        'uang_makan',
        'uang_transport',
        'total_hadir',
        'total_telat',
        'total_alfa',
        'total_izin',
        'total_cuti',
        'potongan_telat',       // khusus karyawan tetap CBN
        'potongan_izin',
        'potongan_bpjs_kes',
        'potongan_bpjs_tk',
        'total_potongan',
        'gaji_bersih',
        'status',
        'diterbitkan_at',
    ];

    protected $casts = [
        'gaji_pokok'        => 'float',
        'uang_makan'        => 'float',
        'uang_transport'    => 'float',
        'potongan_telat'    => 'float',
        'potongan_izin'     => 'float',
        'potongan_bpjs_kes' => 'float',
        'potongan_bpjs_tk'  => 'float',
        'total_potongan'    => 'float',
        'gaji_bersih'       => 'float',
        'diterbitkan_at'    => 'datetime',
    ];

    // ── Relasi ──────────────────────────────────────────────────────

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class);
    }

    public function periodeGaji()
    {
        // Gunakan FK periode_id
        return $this->belongsTo(PeriodeGaji::class, 'periode_id');
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
        return $this->gaji_pokok + $this->uang_makan + $this->uang_transport;
    }

    public function formatRp(float $angka): string
    {
        return 'Rp ' . number_format($angka, 0, ',', '.');
    }
}