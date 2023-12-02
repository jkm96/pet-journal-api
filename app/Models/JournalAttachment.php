<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JournalAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'journal_id',
        'type',
        'source_url'
    ];

    /**
     * Get the JournalEntry that owns the JournalAttachment.
     */
    public function journalEntry()
    {
        return $this->belongsTo(JournalEntry::class);
    }
}
