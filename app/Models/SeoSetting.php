<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

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
}
