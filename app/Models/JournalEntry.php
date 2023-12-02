<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JournalEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'pet_id',
        'title',
        'event',
        'content',
        'location',
        'mood',
        'keywords',
    ];

    /**
     * Get the pets associated with the journal entry.
     */
    public function pets()
    {
        return $this->belongsToMany(Pet::class, 'pet_journal_entries');
    }

    /**
     * Get the JournalAttachment for the JournalEntry.
     */
    public function journalAttachments()
    {
        return $this->hasMany(JournalAttachment::class);
    }
}
