<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Asset extends Model
{
    #[Fillable([
        'name',
        'code_tag_number',
        'category',
        'brand',
        'model',
        'serial_number',
        'purchase_date',
        'purchase_cost',
        'current_value',
        'vendor_supplier',
        'location',
        'assigned_user_id',
        'condition',
        'status',
        'notes',
        'attachment_path',
    ])]

    protected $casts = [
        'purchase_cost' => 'decimal:2',
        'current_value' => 'decimal:2',
    ];

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function scopeFilterByCategory($query, $category)
    {
        if ($category) {
            return $query->where('category', $category);
        }
        return $query;
    }

    public function scopeFilterByStatus($query, $status)
    {
        if ($status) {
            return $query->where('status', $status);
        }
        return $query;
    }

    public function scopeFilterByLocation($query, $location)
    {
        if ($location) {
            return $query->where('location', $location);
        }
        return $query;
    }

    public function scopeFilterByAssignedUser($query, $userId)
    {
        if ($userId) {
            return $query->where('assigned_user_id', $userId);
        }
        return $query;
    }

    public function scopeFilterByPurchaseDateRange($query, $startDate, $endDate)
    {
        if ($startDate && $endDate) {
            return $query->whereBetween('purchase_date', [$startDate, $endDate]);
        }
        return $query;
    }
}
