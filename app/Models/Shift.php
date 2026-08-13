<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasCustomId;

class Shift extends Model
{
    use HasCustomId;

    const ID_PREFIX = 'SFT';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $table = 'shift';

    protected $fillable = [
        'mitra_id',
        'nama_shift',
        'jam_mulai',
        'jam_selesai',
        'window_start',
        'window_end',
        'toleransi_menit',
        'is_lintas_hari',
    ];

    protected $casts = [
        'is_lintas_hari' => 'boolean',
    ];

    /**
     * Cek apakah jam saat ini masuk dalam window absen shift ini.
     */
    public function isInWindow($time): bool
    {
        $now = \Carbon\Carbon::parse($time)->format('H:i:s');
        
        // Kasus window lintas hari (misal 23:00 s/d 02:00)
        if ($this->window_start > $this->window_end) {
            return $now >= $this->window_start || $now <= $this->window_end;
        }

        return $now >= $this->window_start && $now <= $this->window_end;
    }

    public function mitra()
    {
        return $this->belongsTo(Mitra::class);
    }


}
