<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasCustomId;

class DetailGajiKomponen extends Model
{
    use HasCustomId;

    const ID_PREFIX = 'DGK';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $table = 'detail_gaji_komponen';

    protected $fillable = [
        'user_id',
        'slip_gaji_periode_id',
        'komponen_gaji_id',
        'nominal',
    ];

    protected $casts = [
        'nominal' => 'float',
    ];

    /**
     * Create a new Eloquent Collection instance.
     *
     * @param  array  $models
     * @return \App\Collections\DetailGajiKomponenCollection
     */
    public function newCollection(array $models = [])
    {
        return new \App\Collections\DetailGajiKomponenCollection($models);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function slipGaji()
    {
        return $this->belongsTo(SlipGajiPeriode::class, 'slip_gaji_periode_id');
    }

    public function komponenGaji()
    {
        return $this->belongsTo(KomponenGaji::class, 'komponen_gaji_id');
    }

    // Helper to get component name easily
    public function getNamaKomponenAttribute()
    {
        return $this->komponenGaji?->nama_komponen;
    }

    // Helper to get component type easily
    public function getTipeAttribute()
    {
        return $this->komponenGaji?->tipe;
    }
}
