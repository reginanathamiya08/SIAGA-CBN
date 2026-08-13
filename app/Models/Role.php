<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasCustomId;

class Role extends Model
{
    use HasCustomId;

    const ID_PREFIX = 'ROL';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'nama_role',
        'slug',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
