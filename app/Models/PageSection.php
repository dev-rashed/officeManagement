<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PageSection extends Model
{
    #[Fillable([
        'page_slug',
        'section_key',
        'title',
        'subtitle',
        'description',
        'image_path',
        'button_text',
        'button_link',
        'visibility',
        'sort_order',
    ])]

    public function scopeVisible($query)
    {
        return $query->where('visibility', true);
    }

    public function scopeSorted($query)
    {
        return $query->orderBy('sort_order')->orderBy('created_at', 'desc');
    }
}
