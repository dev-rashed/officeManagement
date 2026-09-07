<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'short_description',
        'full_description',
        'featured_image',
        'category_id',
        'duration',
        'class_type',
        'location',
        'schedule_batch',
        'fee',
        'instructor_name',
        'course_outline',
        'sort_order',
        'status',
    ];

    public function category()
    {
        return $this->belongsTo(CourseCategory::class);
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeSorted($query)
    {
        return $query->orderBy('sort_order')->orderBy('created_at', 'desc');
    }
}
