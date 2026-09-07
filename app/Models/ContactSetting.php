<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactSetting extends Model
{
    protected $fillable = [
        'email',
        'phone',
        'address',
        'social_links',
    ];

    protected $casts = [
        'social_links' => 'array',
    ];

    // Static method to get the single instance (or create default)
    public static function instance()
    {
        return self::firstOrCreate([], [
            'email' => 'hello@hashtag.com',
            'phone' => '+880 123-456-7890',
            'address' => 'Rangpur, Bangladesh',
            'social_links' => []
        ]);
    }
}
