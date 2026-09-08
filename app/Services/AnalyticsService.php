<?php

namespace App\Services;

use App\Models\PageView;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Every figure on the analytics dashboard.
 *
 * Bots are excluded from all of it -- they are recorded so the bot share can be
 * shown, but they never count as traffic.
 */
class AnalyticsService
{
    public function __construct(
        private CarbonInterface $from,
        private CarbonInterface $to,
    ) {}

    public static function forRange(CarbonInterface $from, CarbonInterface $to): self
    {
        return new self($from->copy()->startOfDay(), $to->copy()->endOfDay());
    }

    private function base()
    {
        return PageView::query()->human()->between($this->from, $this->to);
    }

    /**
     * Headline numbers, each with the change against the previous period of
     * the same length -- a number with no comparison says very little.
     */
    public function summary(): array
    {
        $days = max(1, $this->from->diffInDays($this->to) + 1);
        $prevTo = $this->from->copy()->subSecond();
        $prevFrom = $prevTo->copy()->subDays($days)->startOfDay();

        $current = [
            'views' => $this->base()->count(),
            'visitors' => $this->base()->distinct('visitor_hash')->count('visitor_hash'),
            'sessions' => $this->base()->whereNotNull('session_hash')->distinct('session_hash')->count('session_hash'),
        ];

        $previous = [
            'views' => PageView::human()->between($prevFrom, $prevTo)->count(),
            'visitors' => PageView::human()->between($prevFrom, $prevTo)->distinct('visitor_hash')->count('visitor_hash'),
            'sessions' => PageView::human()->between($prevFrom, $prevTo)->whereNotNull('session_hash')->distinct('session_hash')->count('session_hash'),
        ];

        return [
            'views' => $this->withChange($current['views'], $previous['views']),
            'visitors' => $this->withChange($current['visitors'], $previous['visitors']),
            'sessions' => $this->withChange($current['sessions'], $previous['sessions']),
            'views_per_visitor' => [
                'value' => $current['visitors'] > 0 ? round($current['views'] / $current['visitors'], 1) : 0,
                'change' => null,
            ],
            'bot_views' => PageView::query()->between($this->from, $this->to)->where('is_bot', true)->count(),
        ];
    }

    private function withChange(int $now, int $before): array
    {
        return [
            'value' => $now,
            'previous' => $before,
            'change' => $before > 0 ? round((($now - $before) / $before) * 100, 1) : null,
        ];
    }

    /**
     * Views and visitors per day, with empty days filled in so the chart has no
     * gaps where nothing happened.
     */
    public function daily(): Collection
    {
        $rows = $this->base()
            ->selectRaw('DATE(viewed_at) as day, COUNT(*) as views, COUNT(DISTINCT visitor_hash) as visitors')
            ->groupBy('day')
            ->orderBy('day')
            ->get()
            ->keyBy('day');

        $out = collect();
        $cursor = $this->from->copy();

        while ($cursor->lte($this->to)) {
            $key = $cursor->toDateString();

            $out->push([
                'date' => $key,
                'label' => $cursor->format('d M'),
                'views' => (int) ($rows[$key]->views ?? 0),
                'visitors' => (int) ($rows[$key]->visitors ?? 0),
            ]);

            // The app binds Date to CarbonImmutable, so addDay() returns a new
            // instance rather than advancing this one. Without reassigning,
            // this loop never ends.
            $cursor = $cursor->addDay();
        }

        return $out;
    }

    public function topPages(int $limit = 12): Collection
    {
        return $this->base()
            ->selectRaw('path, MAX(title) as title, COUNT(*) as views, COUNT(DISTINCT visitor_hash) as visitors')
            ->groupBy('path')
            ->orderByDesc('views')
            ->limit($limit)
            ->get();
    }

    /** Views broken down by the course or service being looked at. */
    public function topContent(string $morphClass, int $limit = 8): Collection
    {
        return $this->base()
            ->where('viewable_type', $morphClass)
            ->selectRaw('viewable_id, MAX(title) as title, COUNT(*) as views, COUNT(DISTINCT visitor_hash) as visitors')
            ->groupBy('viewable_id')
            ->orderByDesc('views')
            ->limit($limit)
            ->get();
    }

    public function topReferrers(int $limit = 8): Collection
    {
        return $this->base()
            ->whereNotNull('referrer_host')
            ->selectRaw('referrer_host, COUNT(*) as views')
            ->groupBy('referrer_host')
            ->orderByDesc('views')
            ->limit($limit)
            ->get();
    }

    public function campaigns(int $limit = 8): Collection
    {
        return $this->base()
            ->whereNotNull('utm_source')
            ->selectRaw('utm_source, utm_medium, utm_campaign, COUNT(*) as views')
            ->groupBy('utm_source', 'utm_medium', 'utm_campaign')
            ->orderByDesc('views')
            ->limit($limit)
            ->get();
    }

    /** @return Collection<int, object{label:string, views:int, share:float}> */
    public function breakdown(string $column): Collection
    {
        $rows = $this->base()
            ->whereNotNull($column)
            ->selectRaw("{$column} as label, COUNT(*) as views")
            ->groupBy($column)
            ->orderByDesc('views')
            ->get();

        $total = max(1, $rows->sum('views'));

        return $rows->map(fn ($r) => (object) [
            'label' => $r->label,
            'views' => (int) $r->views,
            'share' => round(($r->views / $total) * 100, 1),
        ]);
    }

    /** Busiest hour of the day, useful for deciding when to publish. */
    public function byHour(): Collection
    {
        $rows = $this->base()
            ->selectRaw('HOUR(viewed_at) as hour, COUNT(*) as views')
            ->groupBy('hour')
            ->pluck('views', 'hour');

        return collect(range(0, 23))->map(fn ($h) => [
            'hour' => $h,
            'label' => str_pad((string) $h, 2, '0', STR_PAD_LEFT).':00',
            'views' => (int) ($rows[$h] ?? 0),
        ]);
    }

    public function recent(int $limit = 15): Collection
    {
        return $this->base()
            ->latest('viewed_at')
            ->limit($limit)
            ->get(['path', 'title', 'device', 'browser', 'referrer_host', 'viewed_at']);
    }

    /** Whether anything has ever been recorded, to tell "no data" from "no traffic". */
    public static function hasAnyData(): bool
    {
        return PageView::query()->exists();
    }
}
