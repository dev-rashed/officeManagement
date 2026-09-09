<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

#[Fillable([
    'site_name',
    'title_template',
    'default_meta_description',
    'default_og_image',
    'canonical_base_url',
    'twitter_handle',
    'google_analytics_id',
    'google_tag_manager_id',
    'google_site_verification',
    'bing_site_verification',
    'organization_type',
    'organization_logo',
    'favicon',
    'social_profiles',
    'custom_head_snippet',
    'custom_body_snippet',
    'robots_txt',
    'is_indexable',
])]
class SeoSetting extends Model
{
    protected $table = 'seo_settings';

    protected $casts = [
        'social_profiles' => 'array',
        'is_indexable' => 'boolean',
    ];

    /** Schema.org types worth offering for an organisation. */
    public const ORGANIZATION_TYPES = [
        'Organization' => 'Organization',
        'Corporation' => 'Corporation',
        'EducationalOrganization' => 'Educational Organization',
        'LocalBusiness' => 'Local Business',
        'ProfessionalService' => 'Professional Service',
    ];

    /**
     * The single settings row, created on demand so the site never breaks if
     * the seed is missing.
     */
    public static function current(): self
    {
        return static::query()->firstOrCreate([], [
            'title_template' => '{title} | {site}',
            'organization_type' => 'Organization',
            'is_indexable' => true,
        ]);
    }

    public function hasAnalytics(): bool
    {
        return filled($this->google_analytics_id) || filled($this->google_tag_manager_id);
    }

    /**
     * The name, logo and icon that dress every page — the admin sidebar, the
     * auth screens and the public header all read this.
     *
     * Cached because it is needed on every single request; cleared whenever the
     * settings row is saved, so an upload shows up immediately.
     *
     * @return array{name: string, logo: ?string, favicon: ?string, favicon_type: ?string}
     */
    public static function branding(): array
    {
        return Cache::remember(self::BRANDING_CACHE_KEY, now()->addDay(), function () {
            // first(), not current() — rendering a page must never write a row.
            $settings = static::query()->first();

            $url = fn (?string $path) => filled($path)
                ? Storage::disk('public')->url($path)
                : null;

            return [
                'name' => $settings?->site_name ?: config('app.name', 'Laravel'),
                'logo' => $url($settings?->organization_logo),
                'favicon' => $url($settings?->favicon),
                'favicon_type' => self::mimeForIcon($settings?->favicon),
            ];
        });
    }

    private const BRANDING_CACHE_KEY = 'seo.branding';

    /** The <link rel="icon"> type, so the browser does not have to sniff. */
    private static function mimeForIcon(?string $path): ?string
    {
        return match (strtolower(pathinfo((string) $path, PATHINFO_EXTENSION))) {
            'ico' => 'image/x-icon',
            'png' => 'image/png',
            'svg' => 'image/svg+xml',
            'webp' => 'image/webp',
            'jpg', 'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            default => null,
        };
    }

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget(self::BRANDING_CACHE_KEY));
        static::deleted(fn () => Cache::forget(self::BRANDING_CACHE_KEY));
    }
}
