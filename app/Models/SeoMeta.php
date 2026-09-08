<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[Fillable([
    'seoable_id',
    'seoable_type',
    'page_key',
    'meta_title',
    'meta_description',
    'meta_keywords',
    'og_title',
    'og_description',
    'og_image',
    'canonical_url',
    'noindex',
    'nofollow',
    'schema_type',
])]
class SeoMeta extends Model
{
    protected $table = 'seo_meta';

    protected $casts = [
        'noindex' => 'boolean',
        'nofollow' => 'boolean',
    ];

    public function seoable(): MorphTo
    {
        return $this->morphTo();
    }

    /** True when this row covers a static route rather than a model. */
    public function isStaticPage(): bool
    {
        return $this->page_key !== null;
    }
}
