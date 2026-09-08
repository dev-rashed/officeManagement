<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[Fillable([
    'path',
    'route_name',
    'page_key',
    'title',
    'viewable_id',
    'viewable_type',
    'visitor_hash',
    'session_hash',
    'referrer_host',
    'referrer_url',
    'utm_source',
    'utm_medium',
    'utm_campaign',
    'device',
    'browser',
    'platform',
    'is_bot',
    'viewed_at',
])]
class PageView extends Model
{
    use HasFactory;

    protected $casts = [
        'is_bot' => 'boolean',
        'viewed_at' => 'datetime',
    ];

    public function viewable(): MorphTo
    {
        return $this->morphTo();
    }

    /** Real traffic only -- bots are recorded but never counted by default. */
    public function scopeHuman($query)
    {
        return $query->where('is_bot', false);
    }

    public function scopeBetween($query, $from, $to)
    {
        return $query->whereBetween('viewed_at', [$from, $to]);
    }
}
