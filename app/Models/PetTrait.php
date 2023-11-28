<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PetTrait extends Model
{
    use HasFactory;

    protected $fillable = [
        'pet_id',
        'like',
        'dislike'
    ];

    /**
     * Get the pet that owns the pet traits.
     */
    public function pet()
    {
        return $this->belongsTo(Pet::class);
    }
}
