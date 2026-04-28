<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Services\UsernameGeneratorService;

class Karyawan extends Model
{
    protected $table = 'karyawan';

    protected $fillable = [
        'user_id',
        'nama',
        'email',
        'jenis_karyawan',
        'divisi',
        'jabatan',
        'tanggal_masuk',
        'no_hp',
        'gaji_atas_umr',
        'is_shift',
        'uang_makan_by_mitra',
        'is_active',
    ];

    protected $casts = [
        'tanggal_masuk'       => 'date',
        'gaji_atas_umr'       => 'boolean',
        'is_shift'            => 'boolean',
        'uang_makan_by_mitra' => 'boolean',
        'is_active'           => 'boolean',
    ];

    // ── Relasi ───────────────────────────────────────────────────────────

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function dokumen()
    {
        return $this->hasMany(DokumenKaryawan::class);
    }

    public function penempatan()
    {
        return $this->hasMany(Penempatan::class);
    }

    public function penempatanAktif()
    {
        return $this->hasOne(Penempatan::class)
                    ->where('status', 'aktif')
                    ->latest();
    }

    public function jadwalShift()
    {
        return $this->hasMany(JadwalShift::class);
    }

    public function absensi()
    {
        return $this->hasMany(Absensi::class);
    }

    public function kuotaCuti()
    {
        return $this->hasMany(KuotaCuti::class);
    }

    public function perizinan()
    {
        return $this->hasMany(Perizinan::class);
    }

    public function lembur()
    {
        return $this->hasMany(Lembur::class);
    }

    public function dinasLuar()
    {
        return $this->hasMany(DinasLuar::class);
    }

    public function komponenGaji()
    {
        return $this->hasOne(KomponenGaji::class);
    }

    public function slipGaji()
    {
        return $this->hasMany(SlipGaji::class);
    }

    // ── Helper ──────────────────────────────────────────────────────────

    public function isTetap(): bool
    {
        return $this->jenis_karyawan === 'tetap';
    }

    public function isKontrak(): bool
    {
        return $this->jenis_karyawan === 'kontrak';
    }

    public function isDivisiHC(): bool
    {
        return strtolower($this->divisi ?? '') === 'hc';
    }

    public function isDivisiUmum(): bool
    {
        return strtolower($this->divisi ?? '') === 'umum';
    }

    public function kuotaCutiTahunIni(): ?KuotaCuti
    {
        return $this->kuotaCuti()
                    ->where('tahun', now()->year)
                    ->first();
    }

    public function labelDivisi(): string
    {
        return match (strtolower($this->divisi ?? '')) {
            'hc'             => 'HC (Human Capital)',
            'umum'           => 'Umum',
            'keuangan'       => 'Keuangan',
            'koordinator_cs' => 'Koordinator CS',
            'adm_umum'       => 'Administrasi & Umum',
            default          => $this->divisi ?? '-',
        };
    }

    public function dokumenWajib(): ?string
    {
        $map = UsernameGeneratorService::jabatanDenganDokumenKhusus();
        return isset($map[$this->jabatan]) ? $map[$this->jabatan]['jenis'] : null;
    }

    public function sudahUploadDokumenWajib(): bool
    {
        $jenis = $this->dokumenWajib();
        if (!$jenis) return true;
        return $this->dokumen()->where('jenis_dokumen', $jenis)->exists();
    }
}