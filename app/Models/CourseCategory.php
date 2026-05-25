<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseCategory extends Model
{
    #[Fillable(['name', 'slug', 'description', 'status'])]

    public function courses()
    {
        return $this->hasMany(Course::class, 'category_id');
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }
}
