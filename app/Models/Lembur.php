<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasCustomId;

class Lembur extends Model
{
    use HasCustomId;

    const ID_PREFIX = 'LBR';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $table = 'lembur';

    protected $fillable = [
        'user_id',
        'tanggal',
        'jam_mulai',
        'jam_selesai',
        'total_jam',
        'keterangan',
        'status_approval',
        'approved_by',
        'approved_at',
        'alasan_tolak',
    ];

    protected $casts = [
        'tanggal'     => 'date',
        'approved_at' => 'datetime',
        'total_jam'   => 'float',
    ];

    // ── Relasi ──────────────────────────────────────────────────────────

    public function karyawan()
    {
        return $this->belongsTo(User::class, 'user_id');
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
     * Format total jam lembur menjadi "Xj Ym".
     */
    public function formatDurasi(): string
    {
        $jam   = floor($this->total_jam);
        $menit = round(($this->total_jam - $jam) * 60);

        return "{$jam}j {$menit}m";
    }

    /**
     * Hitung total nominal upah & uang makan lembur.
     */
    public function hitungNominal(): float
    {
        $gajiPokok = (float) ($this->karyawan?->komponenGaji?->gaji_pokok ?? 0);
        if ($gajiPokok <= 0) return 0.0;
        
        $upahPerJam = floor($gajiPokok / 173);
        $isHoliday = \App\Helpers\AttendanceHelper::isHolidayOrWeekend($this->tanggal);
        $jamLembur = (float) $this->total_jam;

        $upahLembur = 0.0;
        $uangMakanLembur = 0.0;

        if ($isHoliday) {
            if ($jamLembur <= 8) {
                $upahLembur = round(2.0 * $upahPerJam);
            } elseif ($jamLembur == 9) {
                $upahLembur = round(5.0 * $upahPerJam);
            } else {
                $upahLembur = round(9.0 * $upahPerJam);
            }
            $uangMakanLembur = 40000.0;
        } else {
            $jamPertama = min(1.0, $jamLembur);
            $jamSisa = max(0.0, $jamLembur - 1.0);
            
            $nominalJamPertama = round($jamPertama * 1.5 * $upahPerJam);
            $nominalJamSisa = round($jamSisa * 2.0 * $upahPerJam);
            $upahLembur = $nominalJamPertama + $nominalJamSisa;

            if ($jamLembur >= 4) {
                $uangMakanLembur = 40000.0;
            }
        }

        return $upahLembur + $uangMakanLembur;
    }
}
