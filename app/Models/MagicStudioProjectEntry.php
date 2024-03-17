<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MagicStudioProjectEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'magic_studio_project_id',
        'journal_entry_ids',
    ];


    /**
     * Get the magic studio that owns the project entry.
     */
    public function magicStudioProject()
    {
        return $this->belongsTo(MagicStudioProject::class);
    }
}
