<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Traits\HasCustomId;

class Notification extends Model
{
    use HasFactory, HasCustomId;

    const ID_PREFIX = 'NTF';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'title',
        'message',
        'type',
        'is_read',
        'link',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($notification) {
            if ($notification->link) {
                $notification->link = static::normalizeInternalLink($notification->link);
            }
        });
    }

    /**
     * Normalize internal application notification links to relative URLs.
     */
    public static function normalizeInternalLink(?string $link): ?string
    {
        if ($link === null) {
            return null;
        }

        $link = trim($link);
        if ($link === '') {
            return null;
        }

        // Already relative path starting with '/' (and not '//')
        if (str_starts_with($link, '/') && !str_starts_with($link, '//')) {
            return $link;
        }

        $parsed = parse_url($link);
        if (!$parsed || !isset($parsed['scheme'])) {
            if (str_starts_with($link, '/')) {
                return '/' . ltrim($link, '/');
            }
            return $link;
        }

        $host = strtolower($parsed['host'] ?? '');

        // Known internal hosts to convert to relative paths
        $appUrlHost = parse_url(config('app.url'), PHP_URL_HOST);
        $requestHost = request()->hasHeader('host') ? request()->getHost() : null;

        $internalHosts = array_filter([
            '127.0.0.1',
            'localhost',
            $appUrlHost ? strtolower($appUrlHost) : null,
            $requestHost ? strtolower($requestHost) : null,
            'siaga-cbn.unand.online',
        ]);

        $isInternal = in_array($host, $internalHosts, true);

        if ($isInternal) {
            $path = $parsed['path'] ?? '/';
            if (!str_starts_with($path, '/')) {
                $path = '/' . $path;
            }
            while (str_starts_with($path, '//')) {
                $path = substr($path, 1);
            }

            $query = isset($parsed['query']) ? '?' . $parsed['query'] : '';
            $fragment = isset($parsed['fragment']) ? '#' . $parsed['fragment'] : '';

            return $path . $query . $fragment;
        }

        return $link;
    }

    /**
     * Check if a link is safe for internal redirect
     */
    public static function isSafeRedirectLink(?string $link): bool
    {
        if (empty($link)) {
            return false;
        }

        $normalized = static::normalizeInternalLink($link);
        if (empty($normalized)) {
            return false;
        }

        if (!str_starts_with($normalized, '/') || str_starts_with($normalized, '//') || str_starts_with($normalized, '/\\')) {
            return false;
        }

        if (preg_match('/[\r\n]/', $normalized)) {
            return false;
        }

        return true;
    }

    /**
     * Helper to send notification easily
     */
    public static function send($userId, $title, $message, $type = 'info', $link = null)
    {
        $normalizedLink = static::normalizeInternalLink($link);

        return self::create([
            'user_id' => $userId,
            'title'   => $title,
            'message' => $message,
            'type'    => $type,
            'link'    => $normalizedLink,
        ]);
    }
}
