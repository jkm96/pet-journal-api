<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JournalEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'event',
        'content',
        'location',
        'mood',
        'tags',
        'profile_url',
    ];

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
}
