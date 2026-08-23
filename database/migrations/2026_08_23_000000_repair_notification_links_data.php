<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\Notification;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $notifications = Notification::whereNotNull('link')
            ->where('link', '!=', '')
            ->get();

        foreach ($notifications as $notif) {
            $original = $notif->link;
            $normalized = Notification::normalizeInternalLink($original);

            if ($normalized !== $original) {
                $notif->update(['link' => $normalized]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Data repair is irreversible and safe
    }
};
