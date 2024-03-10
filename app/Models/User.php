<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'username',
        'email',
        'password',
        'profile_url',
        'profile_cover_url',
        'is_active',
    ];

    /**
     * Get the permissions for the user.
     */
    public function permissions()
    {
        return $this->hasMany(Permission::class);
    }

    /**
     * Get the magic studio projects for the user.
     */
    public function magicStudioProjects()
    {
        return $this->hasMany(MagicStudioProject::class);
    }

    /**
     * Get the subscription for the user.
     */
    public function userSubscriptions()
    {
        return $this->hasMany(CustomerSubscription::class, 'customer_id', 'customer_id');
    }

    /**
     * Get the pets for the user.
     */
    public function pets()
    {
        return $this->hasMany(Pet::class);
    }

    /**
     * Get the journal entries for the user.
     */
    public function journalEntries()
    {
        return $this->hasMany(JournalEntry::class);
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
}
