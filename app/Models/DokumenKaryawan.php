<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DokumenKaryawan extends Model
{
    public $timestamps = false;

    protected $table = 'dokumen_karyawan';

    protected $fillable = [
        'karyawan_id',
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
        return $this->belongsTo(Karyawan::class);
    }
}