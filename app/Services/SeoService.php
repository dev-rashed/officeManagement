<?php

namespace App\Services;

use App\Models\SeoMeta;
use App\Models\SeoSetting;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Works out the tags for the page currently being rendered.
 *
 * Resolution order for every value: the page's own SEO row, then something
 * sensible derived from the record itself, then the site-wide default.
 */
class SeoService
{
    /**
     * The static routes that can carry SEO but have no model behind them.
     *
     * Keyed by page_key, valued by [label, route name].
     */
    public const STATIC_PAGES = [
        'home' => ['Home', 'home'],
        'about' => ['About', 'about'],
        'mission' => ['Mission', 'mission'],
        'vision' => ['Vision', 'vision'],
        'team' => ['Team', 'team'],
        'portfolio' => ['Portfolio', 'portfolio'],
        'contact' => ['Contact', 'contact'],
        'courses' => ['Courses index', 'courses.index'],
        'services' => ['Services index', 'services.index'],
    ];

    private ?SeoSetting $settings = null;

    /**
     * The page_key for a route name, or null if that route carries no static
     * SEO row (detail pages resolve through their model instead).
     */
    public static function pageKeyForRoute(?string $routeName): ?string
    {
        if (! $routeName) {
            return null;
        }

        foreach (self::STATIC_PAGES as $key => [$label, $route]) {
            if ($route === $routeName) {
                return $key;
            }
        }

        return null;
    }

    public function settings(): SeoSetting
    {
        return $this->settings ??= SeoSetting::current();
    }

    /**
     * Build the full tag set for a page.
     *
     * @param  string|null  $pageKey  For a static route.
     * @param  Model|null  $model  For a course, service or portfolio item.
     * @param  array{title?:string,description?:string,image?:string}  $fallback
     *         What the page itself suggests when nothing is configured.
     */
    public function resolve(?string $pageKey = null, ?Model $model = null, array $fallback = []): array
    {
        $settings = $this->settings();
        $meta = $this->metaFor($pageKey, $model);

        $rawTitle = $meta?->meta_title
            ?: ($fallback['title'] ?? null)
            ?: $this->guessTitle($model)
            ?: $settings->site_name;

        $description = $meta?->meta_description
            ?: ($fallback['description'] ?? null)
            ?: $this->guessDescription($model)
            ?: $settings->default_meta_description;

        $image = $meta?->og_image
            ?: ($fallback['image'] ?? null)
            ?: $this->guessImage($model)
            ?: $settings->default_og_image;

        // A page that is not itself the site name gets the template applied.
        $title = $this->applyTemplate($rawTitle, $settings);

        $canonical = $meta?->canonical_url ?: $this->canonicalUrl();

        // The global switch wins -- a staging site must never be indexed
        // because one page forgot to opt out.
        $noindex = ! $settings->is_indexable || (bool) $meta?->noindex;

        return [
            'title' => $title,
            'description' => Str::limit((string) $description, 300, ''),
            'keywords' => $meta?->meta_keywords,
            'canonical' => $canonical,
            'noindex' => $noindex,
            'nofollow' => (bool) $meta?->nofollow,
            'og_title' => $meta?->og_title ?: $title,
            'og_description' => $meta?->og_description ?: $description,
            'og_image' => $this->absoluteUrl($image),
            'og_type' => $model ? 'article' : 'website',
            'twitter_handle' => $settings->twitter_handle,
            'schema_type' => $meta?->schema_type,
            'settings' => $settings,
        ];
    }

    public function metaFor(?string $pageKey, ?Model $model): ?SeoMeta
    {
        if ($model) {
            return SeoMeta::query()
                ->where('seoable_type', $model->getMorphClass())
                ->where('seoable_id', $model->getKey())
                ->first();
        }

        if ($pageKey) {
            return SeoMeta::query()->where('page_key', $pageKey)->first();
        }

        return null;
    }

    /**
     * The organisation JSON-LD block, emitted once on every page.
     */
    public function organizationSchema(): ?array
    {
        $settings = $this->settings();

        if (blank($settings->site_name)) {
            return null;
        }

        $schema = [
            '@context' => 'https://schema.org',
            '@type' => $settings->organization_type ?: 'Organization',
            'name' => $settings->site_name,
            'url' => $settings->canonical_base_url ?: config('app.url'),
        ];

        if (filled($settings->organization_logo)) {
            $schema['logo'] = $this->absoluteUrl($settings->organization_logo);
        }

        if (filled($settings->default_meta_description)) {
            $schema['description'] = $settings->default_meta_description;
        }

        $profiles = array_values(array_filter((array) $settings->social_profiles));

        if ($profiles) {
            $schema['sameAs'] = $profiles;
        }

        return $schema;
    }

    private function applyTemplate(?string $title, SeoSetting $settings): string
    {
        $title = trim((string) $title);
        $site = trim((string) $settings->site_name);

        if ($title === '') {
            return $site;
        }

        // Avoid "HashTag | HashTag" when the page title already is the site.
        if ($site === '' || $title === $site) {
            return $title;
        }

        // A title that already mentions the site name is left alone. Hand
        // written titles put it at either end -- "About Us | Site" and
        // "Site | Tagline" both appear in this codebase -- so checking only the
        // end would still double up the second form.
        if (Str::contains($title, $site)) {
            return $title;
        }

        return str_replace(
            ['{title}', '{site}'],
            [$title, $site],
            $settings->title_template ?: '{title} | {site}',
        );
    }

    private function canonicalUrl(): string
    {
        $base = $this->settings()->canonical_base_url;

        if (blank($base)) {
            return url()->current();
        }

        return rtrim($base, '/').'/'.ltrim(request()->path() === '/' ? '' : request()->path(), '/');
    }

    private function absoluteUrl(?string $path): ?string
    {
        if (blank($path)) {
            return null;
        }

        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        return Storage::disk('public')->url($path);
    }

    private function guessTitle(?Model $model): ?string
    {
        return $model?->getAttribute('title') ?? $model?->getAttribute('name');
    }

    private function guessDescription(?Model $model): ?string
    {
        $raw = $model?->getAttribute('short_description')
            ?? $model?->getAttribute('description');

        return $raw ? Str::limit(strip_tags($raw), 160) : null;
    }

    private function guessImage(?Model $model): ?string
    {
        return $model?->getAttribute('featured_image') ?? $model?->getAttribute('image_path');
    }
}
