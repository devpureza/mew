<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wedding extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'event_date',
        'location',
        'location_details',
        'notes',
    ];

    protected $casts = [
        'event_date' => 'date',
    ];

    public function couples()
    {
        return $this->belongsToMany(User::class, 'wedding_user')
            ->withPivot(['role', 'is_primary'])
            ->withTimestamps();
    }

    public function guests()
    {
        return $this->hasMany(Guest::class);
    }
}
