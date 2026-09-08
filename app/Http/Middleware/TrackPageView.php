<?php

namespace App\Http\Middleware;

use App\Models\PageView;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * Records a view of a public page, after the response is built.
 *
 * Deliberately records no raw IP address: the visitor is a salted hash, which
 * is enough to count unique people without the table holding anything that
 * identifies them.
 */
class TrackPageView
{
    /** Substrings that mark a request as automated. */
    private const BOT_SIGNATURES = [
        'bot', 'crawl', 'spider', 'slurp', 'facebookexternalhit', 'ia_archiver',
        'curl', 'wget', 'python-requests', 'httpclient', 'headlesschrome',
        'lighthouse', 'pingdom', 'uptimerobot', 'semrush', 'ahrefs', 'mj12',
        'dotbot', 'petalbot', 'bytespider', 'gptbot', 'ccbot', 'claudebot',
        'phantomjs', 'monitoring', 'preview',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        try {
            $this->record($request, $response);
        } catch (\Throwable $e) {
            // Analytics must never take the site down.
            Log::warning('Page view tracking failed', ['error' => $e->getMessage()]);
        }

        return $response;
    }

    private function record(Request $request, Response $response): void
    {
        if (! $this->shouldTrack($request, $response)) {
            return;
        }

        $agent = (string) $request->userAgent();

        $viewable = $request->attributes->get('trackable');
        $viewable = $viewable instanceof \Illuminate\Database\Eloquent\Model ? $viewable : null;

        PageView::create([
            'path' => Str::limit('/'.ltrim($request->path(), '/'), 250, ''),
            'route_name' => $request->route()?->getName(),
            'page_key' => \App\Services\SeoService::pageKeyForRoute($request->route()?->getName()),
            'title' => $this->titleFrom($response),
            // Detail-page controllers stash the record they rendered, so views
            // can be reported per course or per service without the middleware
            // re-querying by slug.
            'viewable_id' => $viewable?->getKey(),
            'viewable_type' => $viewable?->getMorphClass(),
            'visitor_hash' => $this->visitorHash($request),
            'session_hash' => $this->sessionHash($request),
            'referrer_host' => $this->referrerHost($request),
            'referrer_url' => Str::limit((string) $request->headers->get('referer'), 490, ''),
            'utm_source' => Str::limit((string) $request->query('utm_source'), 95, '') ?: null,
            'utm_medium' => Str::limit((string) $request->query('utm_medium'), 95, '') ?: null,
            'utm_campaign' => Str::limit((string) $request->query('utm_campaign'), 95, '') ?: null,
            'device' => $this->device($agent),
            'browser' => $this->browser($agent),
            'platform' => $this->platform($agent),
            'is_bot' => $this->isBot($agent),
            'viewed_at' => now(),
        ]);
    }

    private function shouldTrack(Request $request, Response $response): bool
    {
        // Only ordinary page loads of the public site.
        if (! $request->isMethod('GET') || $request->ajax() || $request->expectsJson()) {
            return false;
        }

        if ($response->getStatusCode() !== 200) {
            return false;
        }

        // Staff browsing their own site must not inflate the numbers.
        if ($request->user()) {
            return false;
        }

        // robots.txt and sitemap.xml are served by public routes but are not
        // pages anyone reads.
        if (in_array($request->route()?->getName(), ['seo.robots', 'seo.sitemap'], true)) {
            return false;
        }

        $contentType = (string) $response->headers->get('Content-Type');

        return $contentType === '' || str_contains($contentType, 'text/html');
    }

    /**
     * A stable-per-day identifier for one visitor, with no way back to the
     * person: IP + user agent + the app key + today's date, hashed.
     */
    private function visitorHash(Request $request): string
    {
        return hash('sha256', implode('|', [
            $request->ip(),
            $request->userAgent(),
            config('app.key'),
            now()->toDateString(),
        ]));
    }

    private function sessionHash(Request $request): ?string
    {
        $id = $request->hasSession() ? $request->session()->getId() : null;

        return $id ? hash('sha256', $id.config('app.key')) : null;
    }

    private function referrerHost(Request $request): ?string
    {
        $referrer = $request->headers->get('referer');

        if (blank($referrer)) {
            return null;
        }

        $host = parse_url($referrer, PHP_URL_HOST);

        if (! $host || $host === $request->getHost()) {
            return null; // Internal navigation is not a referrer.
        }

        return Str::limit(Str::lower($host), 145, '');
    }

    private function isBot(string $agent): bool
    {
        if ($agent === '') {
            return true;
        }

        $agent = Str::lower($agent);

        foreach (self::BOT_SIGNATURES as $signature) {
            if (str_contains($agent, $signature)) {
                return true;
            }
        }

        return false;
    }

    private function device(string $agent): string
    {
        $agent = Str::lower($agent);

        if (str_contains($agent, 'ipad') || str_contains($agent, 'tablet')) {
            return 'tablet';
        }

        if (str_contains($agent, 'mobi') || str_contains($agent, 'iphone') || str_contains($agent, 'android')) {
            return 'mobile';
        }

        return 'desktop';
    }

    private function browser(string $agent): string
    {
        return match (true) {
            str_contains($agent, 'Edg') => 'Edge',
            str_contains($agent, 'OPR') || str_contains($agent, 'Opera') => 'Opera',
            str_contains($agent, 'Chrome') => 'Chrome',
            str_contains($agent, 'Safari') => 'Safari',
            str_contains($agent, 'Firefox') => 'Firefox',
            default => 'Other',
        };
    }

    private function platform(string $agent): string
    {
        return match (true) {
            str_contains($agent, 'Windows') => 'Windows',
            str_contains($agent, 'iPhone') || str_contains($agent, 'iPad') => 'iOS',
            str_contains($agent, 'Android') => 'Android',
            str_contains($agent, 'Mac OS') => 'macOS',
            str_contains($agent, 'Linux') => 'Linux',
            default => 'Other',
        };
    }

    /** Pull the page title out of the rendered HTML for a readable report. */
    private function titleFrom(Response $response): ?string
    {
        $content = $response->getContent();

        if (! is_string($content) || $content === '') {
            return null;
        }

        if (preg_match('/<title[^>]*>(.*?)<\/title>/is', $content, $m)) {
            return Str::limit(trim(html_entity_decode(strip_tags($m[1]), ENT_QUOTES | ENT_HTML5)), 245, '');
        }

        return null;
    }
}
