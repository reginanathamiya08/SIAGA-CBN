<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasCustomId;

class Absensi extends Model
{
    use HasCustomId;

    const ID_PREFIX = 'ABS';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $table = 'absensi';

    protected $fillable = [
        'user_id',
        'mitra_id',
        'shift_id',
        'tanggal',
        'waktu_masuk',
        'waktu_pulang',
        'lat_masuk',
        'long_masuk',
        'ip_masuk',
        'lat_pulang',
        'long_pulang',
        'ip_pulang',
        'status',
        'is_telat',
    ];

    protected $casts = [
        'tanggal'      => 'date',
        'waktu_masuk'  => 'datetime',
        'waktu_pulang' => 'datetime',
        'is_telat'     => 'boolean',
        'lat_masuk'    => 'float',
        'long_masuk'   => 'float',
        'lat_pulang'   => 'float',
        'long_pulang'  => 'float',
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

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    public function perizinanDisetujui()
    {
        return $this->hasOne(DetailPerizinan::class, 'user_id', 'user_id')
                    ->where('status_approval', 'disetujui')
                    ->whereDate('tanggal_mulai', '<=', $this->tanggal)
                    ->whereDate('tanggal_selesai', '>=', $this->tanggal);
    }

    // ── Helper ──────────────────────────────────────────────────────────

    public function sudahPulang(): bool
    {
        return !is_null($this->waktu_pulang);
    }

    /**
     * Hitung durasi kerja dalam menit.
     */
    public function durasiMenit(): ?int
    {
        if (!$this->waktu_masuk || !$this->waktu_pulang) {
            return null;
        }

        return (int) $this->waktu_masuk->diffInMinutes($this->waktu_pulang);
    }

    public function getLabelStatusAttribute(): string
    {
        if ($this->status === 'hadir' || $this->status === 'telat') {
            return $this->is_telat ? 'Telat' : 'Hadir';
        }

        $perizinan = $this->perizinanDisetujui;
        if ($perizinan && $perizinan->jenisPerizinan) {
            return $perizinan->jenisPerizinan->nama_jenis;
        }

        return $this->status === 'alfa' ? 'Alfa' : ucfirst($this->status);
    }
}
