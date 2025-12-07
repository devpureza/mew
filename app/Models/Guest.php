<?php

namespace App\Models;

use App\Enums\GodparentRole;
use App\Enums\GuestRelationship;
use App\Enums\GuestStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Guest extends Model
{
    use HasFactory;

    protected $fillable = [
        'wedding_id',
        'invited_by_user_id',
        'parent_guest_id',
        'name',
        'cpf',
        'invitation_code',
        'email',
        'phone',
        'status',
        'is_head_of_family',
        'is_godparent',
        'godparent_role',
        'relationship',
        'belongs_to_user_id',
        'party_size',
        'notes',
    ];

    protected $casts = [
        'is_head_of_family' => 'boolean',
        'is_godparent' => 'boolean',
        'party_size' => 'integer',
        'status' => GuestStatus::class,
        'godparent_role' => GodparentRole::class,
        'relationship' => GuestRelationship::class,
    ];

    protected static function booted(): void
    {
        static::creating(function (Guest $guest): void {
            if (! $guest->invitation_code) {
                $guest->invitation_code = static::makeInvitationCode($guest);
            }
        });
    }

    public static function makeInvitationCode(Guest $guest): string
    {
        $namePart = Str::slug($guest->name, '');
        $weddingTitle = $guest->wedding?->title ?? 'mew';
        $weddingPart = Str::slug($weddingTitle, '');
        $random = random_int(1000, 9999);

        return substr($namePart, 0, 6).substr($weddingPart, 0, 6).$random;
    }

    public function wedding()
    {
        return $this->belongsTo(Wedding::class);
    }

    public function invitedBy()
    {
        return $this->belongsTo(User::class, 'invited_by_user_id');
    }

    public function parentGuest()
    {
        return $this->belongsTo(Guest::class, 'parent_guest_id');
    }

    public function children()
    {
        return $this->hasMany(Guest::class, 'parent_guest_id');
    }

    public function belongsToUser()
    {
        return $this->belongsTo(User::class, 'belongs_to_user_id');
    }
}
