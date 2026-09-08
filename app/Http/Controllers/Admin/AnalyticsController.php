<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Service;
use App\Services\AnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;

class AnalyticsController extends Controller
{
    /** Presets offered above the chart. */
    public const RANGES = [
        '7' => 'Last 7 days',
        '30' => 'Last 30 days',
        '90' => 'Last 90 days',
        'month' => 'This month',
        'last_month' => 'Last month',
    ];

    public function index(Request $request)
    {
        Gate::authorize('cms.manage');

        $request->validate([
            'range' => ['nullable', 'string'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        [$from, $to, $range] = $this->resolveRange($request);

        $analytics = AnalyticsService::forRange($from, $to);

        $summary = $analytics->summary();
        $daily = $analytics->daily();
        $topPages = $analytics->topPages();
        $referrers = $analytics->topReferrers();
        $campaigns = $analytics->campaigns();
        $devices = $analytics->breakdown('device');
        $browsers = $analytics->breakdown('browser');
        $platforms = $analytics->breakdown('platform');
        $byHour = $analytics->byHour();
        $recent = $analytics->recent();

        // Resolve the course / service names for the content tables.
        $topCourses = $this->withNames($analytics->topContent((new Course)->getMorphClass()), Course::class, 'title');
        $topServices = $this->withNames($analytics->topContent((new Service)->getMorphClass()), Service::class, 'title');

        $hasAnyData = AnalyticsService::hasAnyData();
        $ranges = self::RANGES;

        return view('pages.admin.analytics.index', compact(
            'summary', 'daily', 'topPages', 'referrers', 'campaigns',
            'devices', 'browsers', 'platforms', 'byHour', 'recent',
            'topCourses', 'topServices', 'from', 'to', 'range', 'ranges', 'hasAnyData',
        ));
    }

    private function resolveRange(Request $request): array
    {
        $range = $request->input('range', '30');

        if ($request->filled('from') && $request->filled('to')) {
            return [Carbon::parse($request->input('from')), Carbon::parse($request->input('to')), 'custom'];
        }

        return match ($range) {
            'month' => [now()->startOfMonth(), now(), 'month'],
            'last_month' => [now()->subMonthNoOverflow()->startOfMonth(), now()->subMonthNoOverflow()->endOfMonth(), 'last_month'],
            '7', '90' => [now()->subDays((int) $range - 1), now(), $range],
            default => [now()->subDays(29), now(), '30'],
        };
    }

    /**
     * The stored title is a snapshot of what the page said at the time; the
     * current record name is more useful, so prefer it when it still exists.
     */
    private function withNames($rows, string $model, string $column)
    {
        $names = $model::query()
            ->whereIn('id', $rows->pluck('viewable_id'))
            ->pluck($column, 'id');

        return $rows->map(function ($row) use ($names) {
            $row->display_name = $names[$row->viewable_id] ?? $row->title ?? 'Deleted item';
            $row->still_exists = isset($names[$row->viewable_id]);

            return $row;
        });
    }
}
