<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'short_description',
        'full_description',
        'featured_image',
        'service_category_id',
        'status',
        'show_on_homepage',
        'sort_order',
    ];

    public function category()
    {
        return $this->belongsTo(ServiceCategory::class, 'service_category_id');
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeVisible($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeSorted($query)
    {
        return $query->orderBy('sort_order')->orderBy('created_at', 'desc');
    }
}
