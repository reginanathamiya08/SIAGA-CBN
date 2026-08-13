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
        $karyawan = Auth::user()->karyawan;
        $notif = Notification::where('id', $id)
                             ->where('user_id', $karyawan->id)
                             ->firstOrFail();

        $notif->update(['is_read' => true]);

        if ($notif->link) {
            return redirect($notif->link);
        }

        return back();
    }

    /**
     * Mark all as read
     */
    public function markAllRead()
    {
        $karyawan = Auth::user()->karyawan;
        Notification::where('user_id', $karyawan->id)
                    ->where('is_read', false)
                    ->update(['is_read' => true]);

        return back()->with('success', 'Semua notifikasi ditandai telah dibaca.');
    }
}
