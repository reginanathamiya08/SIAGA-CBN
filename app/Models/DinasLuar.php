<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DinasLuar extends Model
{
    protected $table = 'dinas_luar';

    protected $fillable = [
        'karyawan_id',
        'tujuan',
        'tanggal_berangkat',
        'tanggal_kembali',
        'file_surat_tugas',
        'status_approval',
        'approved_by',
        'approved_at',
        'alasan_tolak',
    ];

    protected $casts = [
        'tanggal_berangkat' => 'date',
        'tanggal_kembali'   => 'date',
        'approved_at'       => 'datetime',
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
     * Hitung jumlah hari dinas luar kota.
     */
    public function jumlahHari(): int
    {
        return (int) $this->tanggal_berangkat->diffInDays($this->tanggal_kembali) + 1;
    }
}