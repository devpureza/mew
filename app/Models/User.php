<?php

namespace App\Models;

use App\Enums\UserRole;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'role',
        'cpf',
        'birth_date',
        'address_line',
        'address_line_two',
        'city',
        'state',
        'postal_code',
        'country',
        'photo_path',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'birth_date' => 'date',
            'role' => UserRole::class,
        ];
    }

    public function weddings()
    {
        return $this->belongsToMany(Wedding::class, 'wedding_user')
            ->withPivot(['role', 'is_primary'])
            ->withTimestamps();
    }

    public function invitedGuests()
    {
        return $this->hasMany(Guest::class, 'invited_by_user_id');
    }
}
