<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class MagicStudioProject extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'content',
        'period_from',
        'period_to',
    ];

    /**
     * Get the user that owns the magic studio project.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the magic studio project entries for the magic studio.
     */
    public function magicStudioProjectEntries()
    {
        return $this->hasMany(MagicStudioProjectEntry::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($project) {
            $slug = Str::slug($project->title);
            $uniqueSlug = $slug;

            $counter = 1;
            while (static::where('slug', $uniqueSlug)->exists()) {
                $uniqueSlug = $slug . '-' . $counter;
                $counter++;
            }
            $project->slug = $uniqueSlug;
        });
    }
}
