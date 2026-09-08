<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\PortfolioItem;
use App\Models\SeoMeta;
use App\Models\SeoSetting;
use App\Models\Service;
use App\Services\SeoService;
use Illuminate\Http\Response;

class SeoController extends Controller
{
    /**
     * robots.txt, served from the database so it is editable in the admin.
     */
    public function robots(): Response
    {
        $settings = SeoSetting::current();

        // A site switched to not-indexable must say so here too, or crawlers
        // will keep coming back from links they already know about.
        $body = $settings->is_indexable
            ? ($settings->robots_txt ?: "User-agent: *\nAllow: /\n")
            : "User-agent: *\nDisallow: /\n";

        $body = rtrim($body)."\n\nSitemap: ".url('sitemap.xml')."\n";

        return response($body, 200)->header('Content-Type', 'text/plain; charset=UTF-8');
    }

    /**
     * sitemap.xml, built from the published content.
     */
    public function sitemap(SeoService $seo): Response
    {
        $urls = [];

        // Static pages, unless their SEO row marks them noindex.
        $noindexKeys = SeoMeta::query()
            ->whereNotNull('page_key')
            ->where('noindex', true)
            ->pluck('page_key')
            ->all();

        foreach (SeoService::STATIC_PAGES as $key => [$label, $routeName]) {
            if (in_array($key, $noindexKeys, true)) {
                continue;
            }

            $urls[] = [
                'loc' => route($routeName),
                'priority' => $key === 'home' ? '1.0' : '0.8',
                'changefreq' => $key === 'home' ? 'weekly' : 'monthly',
                'lastmod' => null,
            ];
        }

        $hidden = SeoMeta::query()
            ->whereNotNull('seoable_id')
            ->where('noindex', true)
            ->get()
            ->groupBy('seoable_type')
            ->map(fn ($rows) => $rows->pluck('seoable_id')->all());

        $skip = fn (string $type, int $id): bool => in_array($id, $hidden[$type] ?? [], true);

        foreach (Course::where('status', 'published')->get() as $course) {
            if ($skip(Course::class, $course->id)) {
                continue;
            }

            $urls[] = [
                'loc' => route('courses.show', $course->slug),
                'priority' => '0.7',
                'changefreq' => 'monthly',
                'lastmod' => $course->updated_at?->toAtomString(),
            ];
        }

        foreach (Service::where('status', 'published')->get() as $service) {
            if ($skip(Service::class, $service->id)) {
                continue;
            }

            $urls[] = [
                'loc' => route('services.show', $service->slug),
                'priority' => '0.7',
                'changefreq' => 'monthly',
                'lastmod' => $service->updated_at?->toAtomString(),
            ];
        }

        $xml = view('seo.sitemap', compact('urls'))->render();

        return response($xml, 200)->header('Content-Type', 'application/xml; charset=UTF-8');
    }
}
