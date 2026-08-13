<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasCustomId;

class DetailPerizinan extends Model
{
    use HasCustomId;

    const ID_PREFIX = 'IZN';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $table = 'detail_perizinan';

    protected $fillable = [
        'user_id',
        'kuota_perizinan_id',
        'jenis_perizinan_id',
        'rekan_kerja_id',
        'tanggal_mulai',
        'tanggal_selesai',
        'jumlah_hari',
        'keterangan',
        'file_bukti',
        'status_approval',
        'status_rekan',
        'rekan_approved_at',
        'approved_by',
        'approved_at',
        'alasan_tolak',
        'slip_gaji_periode_id',
    ];

    protected $casts = [
        'tanggal_mulai'     => 'date',
        'tanggal_selesai'   => 'date',
        'approved_at'       => 'datetime',
        'rekan_approved_at' => 'datetime',
    ];

    // ── Relasi ──────────────────────────────────────────────────────────

    public function kuotaPerizinan()
    {
        return $this->belongsTo(KuotaPerizinan::class, 'kuota_perizinan_id');
    }

    public function jenisPerizinan()
    {
        return $this->belongsTo(JenisPerizinan::class, 'jenis_perizinan_id');
    }

    public function karyawan()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function rekanKerja()
    {
        return $this->belongsTo(User::class, 'rekan_kerja_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function slipGaji()
    {
        return $this->belongsTo(SlipGajiPeriode::class, 'slip_gaji_periode_id');
    }

    // ── Helper ──────────────────────────────────────────────────────────

    /**
     * Apakah jenis izin ini memotong kuota perizinan?
     */
    public function memotongCuti(): bool
    {
        return $this->jenisPerizinan?->memotong_kuota ?? false;
    }

    /**
     * Apakah jenis izin ini memotong uang makan?
     */
    public function memotongUangMakan(): bool
    {
        return $this->jenisPerizinan?->memotong_uang_makan ?? false;
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
        return $this->jenisPerizinan?->nama_jenis ?? '';
    }
}
