<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceCategory extends Model
{
    protected $fillable = ['name', 'slug', 'description', 'status'];

    public function services()
    {
        return $this->hasMany(Service::class, 'service_category_id');
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }
}
