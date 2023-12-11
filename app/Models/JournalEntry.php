<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class JournalEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'event',
        'content',
        'location',
        'mood',
        'tags',
        'slug',
    ];

    /**
     * Get the user associated with the journal entry.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the pets associated with the journal entry.
     */
    public function pets()
    {
        return $this->belongsToMany(Pet::class, 'pet_journal_entries', 'journal_entry_id', 'pet_id');
    }

    /**
     * Get the JournalAttachment for the JournalEntry.
     */
    public function journalAttachments()
    {
        return $this->hasMany(JournalAttachment::class);
    }

    protected static function boot()
    {
        parent::boot();

        // Use the 'creating' event to generate and set the unique slug
        static::creating(function ($journal) {
            $slug = Str::slug($journal->title);
            $uniqueSlug = $slug;

            // Check for uniqueness and append a number if needed
            $counter = 1;
            while (static::where('slug', $uniqueSlug)->exists()) {
                $uniqueSlug = $slug . '-' . $counter;
                $counter++;
            }
            $journal->user_id = auth()->user()->getAuthIdentifier();
            $journal->slug = $uniqueSlug;
        });
    }
}
