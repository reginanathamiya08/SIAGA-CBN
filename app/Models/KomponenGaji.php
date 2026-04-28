<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KomponenGaji extends Model
{
    protected $table = 'komponen_gaji';

    protected $fillable = [
        'karyawan_id',
        'gaji_pokok',
        'uang_makan',
        'uang_transport',
        'persen_bpjs_kes',
        'persen_bpjs_tk',
        'updated_by',
    ];

    protected $casts = [
        'gaji_pokok'      => 'float',
        'uang_makan'      => 'float',
        'uang_transport'  => 'float',
        'persen_bpjs_kes' => 'float',
        'persen_bpjs_tk'  => 'float',
    ];

    // ── Relasi ──────────────────────────────────────────────────────────

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class);
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // ── Helper perhitungan ───────────────────────────────────────────────

    /**
     * Hitung nominal potongan BPJS Kesehatan (9.24% dari gaji pokok).
     */
    public function hitungBpjsKes(): float
    {
        return round($this->gaji_pokok * ($this->persen_bpjs_kes / 100), 2);
    }

    /**
     * Hitung nominal potongan BPJS Ketenagakerjaan (5% dari gaji pokok).
     */
    public function hitungBpjsTk(): float
    {
        return round($this->gaji_pokok * ($this->persen_bpjs_tk / 100), 2);
    }

    /**
     * Total potongan BPJS (kesehatan + ketenagakerjaan).
     */
    public function totalPotonganBpjs(): float
    {
        return $this->hitungBpjsKes() + $this->hitungBpjsTk();
    }

    /**
     * Total tunjangan harian (uang makan + transport).
     * Digunakan untuk menghitung potongan saat telat/alfa.
     */
    public function tunjanganHarian(): float
    {
        return ($this->uang_makan ?? 0) + ($this->uang_transport ?? 0);
    }
}