<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VillaListing extends Model
{
    protected $fillable = [
        'slug', 'name', 'location', 'location_desc',
        'beds', 'guests', 'description', 'image', 'gallery', 'status',
    ];

    protected $casts = [
        'gallery' => 'array',
    ];

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
