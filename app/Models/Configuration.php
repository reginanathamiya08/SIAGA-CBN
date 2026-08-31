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
     * Set or update configuration value by key
     */
    public static function setValue($key, $value, $label = null, $type = 'string', $group = 'general')
    {
        $config = self::where('key', $key)->first();
        if ($config) {
            $config->value = $value;
            if ($label !== null) $config->label = $label;
            if ($type !== null)  $config->type  = $type;
            if ($group !== null) $config->group = $group;
            $config->save();
        } else {
            $config = self::create([
                'key'   => $key,
                'value' => $value,
                'label' => $label ?? ucfirst(str_replace('_', ' ', $key)),
                'type'  => $type,
                'group' => $group,
            ]);
        }

        Cache::forget("config_{$key}");

        return $config;
    }

    /**
     * Clear cache when updated or deleted
     */
    protected static function booted()
    {
        static::saved(function ($config) {
            Cache::forget("config_{$config->key}");
        });
        static::deleted(function ($config) {
            Cache::forget("config_{$config->key}");
        });
    }
}
