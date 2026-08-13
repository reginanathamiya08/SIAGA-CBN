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

    /**
     * Helper to send notification easily
     */
    public static function send($userId, $title, $message, $type = 'info', $link = null)
    {
        return self::create([
            'user_id' => $userId,
            'title'   => $title,
            'message' => $message,
            'type'    => $type,
            'link'    => $link,
        ]);
    }
}
