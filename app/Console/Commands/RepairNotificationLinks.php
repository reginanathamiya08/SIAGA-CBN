<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Notification;

class RepairNotificationLinks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:repair-links';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Repair legacy absolute or environment-dependent notification links to relative URLs.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $notifications = Notification::whereNotNull('link')
            ->where('link', '!=', '')
            ->get();

        $count = 0;
        foreach ($notifications as $notif) {
            $original = $notif->link;
            $normalized = Notification::normalizeInternalLink($original);

            if ($normalized !== $original) {
                $notif->update(['link' => $normalized]);
                $count++;
            }
        }

        $this->info("Successfully checked {$notifications->count()} notifications and repaired {$count} records.");
        return 0;
    }
}
