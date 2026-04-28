<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Perizinan extends Model
{
    protected $table = 'perizinan';

    protected $fillable = [
        'karyawan_id',
        'jenis_izin',
        'tanggal_mulai',
        'tanggal_selesai',
        'jumlah_hari',
        'keterangan',
        'file_bukti',
        'status_approval',
        'approved_by',
        'approved_at',
        'alasan_tolak',
    ];

    protected $casts = [
        'tanggal_mulai'   => 'date',
        'tanggal_selesai' => 'date',
        'approved_at'     => 'datetime',
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

    /**
     * Apakah jenis izin ini memotong kuota cuti?
     * izin_pribadi dan sakit_no_surat → memotong cuti.
     */
    public function memotongCuti(): bool
    {
        return in_array($this->jenis_izin, ['izin_pribadi', 'sakit_no_surat']);
    }

    /**
     * Apakah jenis izin ini memotong uang makan?
     * Cuti → potong uang makan Rp 35.000/hari.
     */
    public function memotongUangMakan(): bool
    {
        return $this->jenis_izin === 'cuti';
    }

    /**
     * Apakah pengajuan ini sedang menunggu approval?
     */
    public function isMenunggu(): bool
    {
        return $this->status_approval === 'menunggu';
    }

    /**
     * Apakah pengajuan ini sudah disetujui?
     */
    public function isDisetujui(): bool
    {
        return $this->status_approval === 'disetujui';
    }

    /**
     * Apakah pengajuan ini ditolak?
     */
    public function isDitolak(): bool
    {
        return $this->status_approval === 'ditolak';
    }

    /**
     * Label jenis izin yang mudah dibaca.
     */
    public function labelJenis(): string
    {
        return match($this->jenis_izin) {
            'cuti'           => 'Cuti Tahunan',
            'izin_pribadi'   => 'Izin Pribadi',
            'sakit_surat'    => 'Sakit (Dengan Surat Dokter)',
            'sakit_no_surat' => 'Sakit (Tanpa Surat Dokter)',
            default          => $this->jenis_izin,
        };
    }
}