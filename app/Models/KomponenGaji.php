<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasCustomId;

class KomponenGaji extends Model
{
    use HasCustomId;

    const ID_PREFIX = 'MKG'; // We can keep MKG as prefix or change to KMP. Let's keep MKG since migration didn't change ID generation
    public $incrementing = false;
    protected $keyType = 'string';
    protected $table = 'komponen_gaji';

    protected $fillable = [
        'nama_komponen',
        'tipe',
    ];

    public function details()
    {
        return $this->hasMany(DetailGajiKomponen::class, 'komponen_gaji_id');
    }
}
