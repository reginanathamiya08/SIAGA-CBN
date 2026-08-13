<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Configuration extends Model
{
    protected $fillable = ['key', 'label', 'value', 'type', 'group'];

    /**
     * Get configuration value by key with caching
     */
    public static function getValue($key, $default = null)
    {
        return Cache::rememberForever("config_{$key}", function () use ($key, $default) {
            $config = self::where('key', $key)->first();
            return $config ? $config->value : $default;
        });
    }

    /**
     * Clear cache when updated
     */
    protected static function booted()
    {
        static::updated(function ($config) {
            Cache::forget("config_{$config->key}");
        });
    }
}
