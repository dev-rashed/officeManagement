<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeamMember extends Model
{
    #[Fillable([
        'name',
        'position',
        'bio',
        'image_path',
        'email',
        'phone',
        'social_links',
        'status',
        'sort_order',
    ])]

    protected $casts = [
        'social_links' => 'array',
    ];

    public function scopeVisible($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeSorted($query)
    {
        return $query->orderBy('sort_order')->orderBy('created_at', 'desc');
    }
}
