<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasCustomId;

class JenisPerizinan extends Model
{
    use HasCustomId;

    const ID_PREFIX = 'JNS';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $table = 'jenis_perizinan';

    protected $fillable = [
        'slug',
        'nama_jenis',
        'memotong_kuota',
        'memotong_uang_makan',
        'wajib_upload_bukti',
    ];

    protected $casts = [
        'memotong_kuota'      => 'boolean',
        'memotong_uang_makan' => 'boolean',
        'wajib_upload_bukti'  => 'boolean',
    ];

    public function detailPerizinan()
    {
        return $this->hasMany(DetailPerizinan::class, 'jenis_perizinan_id');
    }
}
