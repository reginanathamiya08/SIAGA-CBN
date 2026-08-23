<?php

namespace App\Http\Controllers\Karyawan;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Mark a notification as read and redirect to link if available
     */
    public function read($id)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $notif = Notification::where('id', $id)
                             ->where('user_id', $user->id)
                             ->firstOrFail();

        $notif->update(['is_read' => true]);

        if ($notif->link) {
            $link = Notification::normalizeInternalLink($notif->link);
            if (Notification::isSafeRedirectLink($link)) {
                return redirect($link);
            }
        }

        return back();
    }

    /**
     * Mark all as read
     */
    public function markAllRead()
    {
        $user = Auth::user();
        if ($user) {
            Notification::where('user_id', $user->id)
                        ->where('is_read', false)
                        ->update(['is_read' => true]);
        }

        return back()->with('success', 'Semua notifikasi ditandai telah dibaca.');
    }
}
