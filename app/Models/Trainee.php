<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'created_by',
    'first_name',
    'last_name',
    'nid',
    'email',
    'phone',
    'date_of_birth',
    'photo_path',
    'father_name',
    'mother_name',
    'emergency_contact_number',
])]
class Trainee extends Model
{
    use HasFactory;

    protected $casts = [
        'date_of_birth' => 'date',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function fullName(): string
    {
        return trim($this->first_name.' '.($this->last_name ?? ''));
    }
}
