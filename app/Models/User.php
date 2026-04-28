<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'username',
        'password',
        'role',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Override: gunakan 'username' sebagai field autentikasi,
     * bukan 'email' (default Laravel).
     */
    public function getAuthIdentifierName(): string
    {
        return 'username';
    }

    // ── Relasi ──────────────────────────────────────────────────

    public function karyawan()
    {
        return $this->hasOne(Karyawan::class);
    }

    // ── Helper role ─────────────────────────────────────────────

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isPimpinan(): bool
    {
        return $this->role === 'pimpinan';
    }

    public function isKaryawanTetap(): bool
    {
        return $this->role === 'karyawan_tetap';
    }

    public function isKaryawanKontrak(): bool
    {
        return $this->role === 'karyawan_kontrak';
    }

    public function isKaryawan(): bool
    {
        return in_array($this->role, ['karyawan_tetap', 'karyawan_kontrak']);
    }
}