<?php

namespace App\Console\Commands;

use App\Models\PageView;
use Illuminate\Console\Command;

/**
 * Page views are one row per visit, so the table grows forever unless it is
 * trimmed. Schedule this daily.
 */
class PrunePageViews extends Command
{
    protected $signature = 'analytics:prune {--days=400 : Keep this many days of history}';

    protected $description = 'Delete page view records older than the retention window';

    public function handle(): int
    {
        $days = max(1, (int) $this->option('days'));
        $cutoff = now()->subDays($days);

        // Deleted in batches so a large backlog never locks the table.
        $total = 0;

        do {
            $deleted = PageView::where('viewed_at', '<', $cutoff)->limit(5000)->delete();
            $total += $deleted;
        } while ($deleted > 0);

        $this->info("Removed {$total} page view(s) older than {$days} days (before {$cutoff->toDateString()}).");

        return self::SUCCESS;
    }
}
