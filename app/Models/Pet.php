<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pet extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'nick_name',
        'species',
        'breed',
        'description',//personality description
        'date_of_birth',
        'profile_url'
    ];

    /**
     * Get the pet traits for the pet - likes, dislikes.
     */
    public function pet_traits()
    {
        return $this->hasMany(PetTrait::class);
    }

    /**
     * Get the journal entries for the pet.
     */
    public function journal_entries()
    {
        return $this->hasMany(JournalEntry::class);
    }

    /**
     * Get the user that owns the pet.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
