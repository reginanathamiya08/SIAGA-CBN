<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasCustomId;

class DokumenUser extends Model
{
    use HasCustomId;

    const ID_PREFIX = 'DOC';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $table = 'dokumen_karyawan';

    protected $fillable = [
        'user_id',
        'jenis_dokumen',
        'file_path',
        'uploaded_at',
    ];

    protected $casts = [
        'uploaded_at' => 'datetime',
    ];

    // ── Relasi ──────────────────────────────────────────────────────────

    public function karyawan()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
