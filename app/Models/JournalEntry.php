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
     * Get the JournalAttachment for the JournalEntry.
     */
    public function journal_attachments()
    {
        return $this->hasMany(JournalAttachment::class);
    }
}
