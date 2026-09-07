<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PortfolioItem extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'description',
        'image_path',
        'technologies',
        'link',
        'status',
        'sort_order',
    ];

    protected $casts = [
        'technologies' => 'array',
    ];

    public function scopeVisible($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeSorted($query)
    {
        return $query->orderBy('sort_order')->orderBy('created_at', 'desc');
    }
}
