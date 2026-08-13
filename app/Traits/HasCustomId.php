<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

trait HasCustomId
{
    /**
     * Boot function dari Laravel model.
     * Akan terpanggil otomatis saat data akan dibuat (creating).
     */
    protected static function bootHasCustomId()
    {
        static::creating(function ($model) {
            // Jika ID belum diisi manual
            if (!$model->getKey()) {
                $model->{$model->getKeyName()} = static::generateCustomId();
            }
        });
    }

    /**
     * Logika untuk generate ID otomatis.
     * Format: PREFIX-00001
     */
    public static function generateCustomId()
    {
        // Ambil prefix dari model (misal: 'KRY')
        $prefix = defined('static::ID_PREFIX') ? static::ID_PREFIX : 'DATA';
        $table = (new static)->getTable();
        $keyName = (new static)->getKeyName();

        // Cari ID terakhir dari tabel tersebut
        $lastRecord = DB::table($table)
            ->where($keyName, 'LIKE', $prefix . '-%')
            ->orderBy($keyName, 'desc')
            ->first();

        if (!$lastRecord) {
            $number = 1;
        } else {
            // Ambil angka dari ID terakhir (misal KRY-00005 -> ambil 5)
            $lastId = $lastRecord->$keyName;
            $lastNumber = (int) Str::after($lastId, $prefix . '-');
            $number = $lastNumber + 1;
        }

        // Gabungkan kembali dengan padding 5 digit nol
        return $prefix . '-' . str_pad($number, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Beritahu Laravel bahwa kita tidak pakai auto-increment.
     */
    public function getIncrementing()
    {
        return false;
    }

    /**
     * Beritahu Laravel bahwa tipe ID kita adalah string.
     */
    public function getKeyType()
    {
        return 'string';
    }
}
