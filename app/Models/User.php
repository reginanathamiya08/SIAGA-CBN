<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Traits\HasCustomId;
use App\Services\UsernameGeneratorService;

class User extends Authenticatable
{
    use Notifiable, HasCustomId;

    const ID_PREFIX = 'KRY'; // Tetap pakai KRY sesuai identitas karyawan
    public $incrementing = false;
    protected $keyType = 'string';
    protected $table = 'users';

    protected $fillable = [
        'role_id',
        'nip',
        'password',
        'nama',
        'email',
        'divisi',
        'jabatan',
        'pendidikan',
        'tanggal_masuk',
        'no_hp',
        'gaji_atas_umr',
        'is_shift',
        'uang_makan_by_mitra',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'tanggal_masuk'       => 'date',
        'gaji_atas_umr'       => 'boolean',
        'is_shift'            => 'boolean',
        'uang_makan_by_mitra' => 'boolean',
        'is_active'           => 'boolean',
    ];

    // ── Relasi ───────────────────────────────────────────────────────────

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function dokumen()
    {
        return $this->hasMany(DokumenUser::class, 'user_id');
    }

    public function penempatan()
    {
        return $this->hasMany(DetailRiwayatPenempatan::class, 'user_id');
    }

    public function penempatanAktif()
    {
        return $this->hasOne(DetailRiwayatPenempatan::class, 'user_id')
                    ->where('status', 'aktif')
                    ->latest();
    }



    public function absensi()
    {
        return $this->hasMany(Absensi::class, 'user_id');
    }

    public function kuotaPerizinan()
    {
        return $this->hasMany(KuotaPerizinan::class, 'user_id');
    }

    public function perizinan()
    {
        return $this->hasMany(DetailPerizinan::class, 'user_id');
    }

    public function lembur()
    {
        return $this->hasMany(Lembur::class, 'user_id');
    }


    public function komponenGaji()
    {
        return $this->hasMany(DetailGajiKomponen::class, 'user_id')->whereNull('slip_gaji_periode_id');
    }

    public function getGajiPokokAttribute(): float
    {
        return $this->komponenGaji->gaji_pokok;
    }

    public function getUangMakanAttribute(): ?float
    {
        return $this->komponenGaji->uang_makan;
    }

    public function getUangTransportAttribute(): ?float
    {
        return $this->komponenGaji->uang_transport;
    }

    public function getPersenBpjsKesAttribute(): float
    {
        return $this->komponenGaji->persen_bpjs_kes;
    }

    public function getPersenBpjsTkAttribute(): float
    {
        return $this->komponenGaji->persen_bpjs_tk;
    }

    public function updateKomponenGaji(array $data)
    {
        $components = [
            'MKG-00001' => isset($data['gaji_pokok']) ? (float) $data['gaji_pokok'] : null,
            'MKG-00003' => isset($data['uang_makan']) ? (float) $data['uang_makan'] : null,
            'MKG-00004' => isset($data['uang_transport']) ? (float) $data['uang_transport'] : null,
            'MKG-00009' => isset($data['persen_bpjs_kes']) ? (float) $data['persen_bpjs_kes'] : null,
            'MKG-00010' => isset($data['persen_bpjs_tk']) ? (float) $data['persen_bpjs_tk'] : null,
        ];

        foreach ($components as $compId => $val) {
            $keyMap = [
                'MKG-00001' => 'gaji_pokok',
                'MKG-00003' => 'uang_makan',
                'MKG-00004' => 'uang_transport',
                'MKG-00009' => 'persen_bpjs_kes',
                'MKG-00010' => 'persen_bpjs_tk',
            ];
            $inputKey = $keyMap[$compId];
            if (!array_key_exists($inputKey, $data)) {
                continue;
            }

            if ($val === null) {
                $this->komponenGaji()->where('komponen_gaji_id', $compId)->delete();
            } else {
                $exists = $this->komponenGaji()->where('komponen_gaji_id', $compId)->first();
                if ($exists) {
                    $exists->update(['nominal' => $val]);
                } else {
                    $this->komponenGaji()->create([
                        'komponen_gaji_id' => $compId,
                        'nominal' => $val,
                    ]);
                }
            }
        }
    }

    public function slipGaji()
    {
        return $this->hasMany(SlipGajiPeriode::class, 'user_id');
    }

    // ── Helper Role ─────────────────────────────────────────────────────

    public function isAdmin(): bool
    {
        return $this->role?->slug === 'admin';
    }

    public function isPimpinan(): bool
    {
        return $this->role?->slug === 'pimpinan';
    }

    public function isKaryawanTetap(): bool
    {
        return $this->role?->slug === 'karyawan_tetap';
    }

    public function isKaryawanKontrak(): bool
    {
        return $this->role?->slug === 'karyawan_kontrak';
    }

    public function isKaryawan(): bool
    {
        return in_array($this->role?->slug, ['karyawan_tetap', 'karyawan_kontrak']);
    }

    // ── Helper Karyawan ──────────────────────────────────────────────────

    public function isTetap(): bool
    {
        return $this->isKaryawanTetap();
    }

    public function isKontrak(): bool
    {
        return $this->isKaryawanKontrak();
    }

    public function isDivisiHC(): bool
    {
        return strtolower($this->divisi ?? '') === 'hc';
    }

    public function isDivisiUmum(): bool
    {
        return strtolower($this->divisi ?? '') === 'umum';
    }

    public function kuotaPerizinanTahunIni(): ?KuotaPerizinan
    {
        return $this->kuotaPerizinan()
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

    // ── Compatibility Hack ───────────────────────────────────────────────
    // Agar kode lama yang memanggil $user->karyawan tidak error.
    public function getKaryawanAttribute() { return $this; }
    public function getUserAttribute() { return $this; }
    public function kuotaCuti() { return $this->kuotaPerizinan(); }
    public function kuotaCutiTahunIni() { return $this->kuotaPerizinanTahunIni(); }
}
