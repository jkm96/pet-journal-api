<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Pet extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'nickname',
        'species',
        'breed',
        'description',//personality description
        'date_of_birth',
        'profile_url',
        'slug',
    ];

    /**
     * Get the pet traits for the pet - likes, dislikes.
     */
    public function petTraits()
    {
        return $this->hasMany(PetTrait::class);
    }

    /**
     * Get the journal entries for the pet.
     */
    public function journalEntries()
    {
        return $this->belongsToMany(JournalEntry::class, 'pet_journal_entries', 'pet_id', 'journal_entry_id');
    }

    /**
     * Get the user that owns the pet.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        // Use the 'creating' event to generate and set the unique slug
        static::creating(function ($pet) {
            $slug = Str::slug($pet->name);
            $uniqueSlug = $slug;

            // Check for uniqueness and append a number if needed
            $counter = 1;
            while (static::where('slug', $uniqueSlug)->exists()) {
                $uniqueSlug = $slug . '-' . $counter;
                $counter++;
            }

            $pet->slug = $uniqueSlug;
            $pet->user_id = auth()->user()->getAuthIdentifier();
        });
    }
}
