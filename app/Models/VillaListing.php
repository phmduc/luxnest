<?php

namespace App\Models;

use App\Traits\HasVideoField;
use Illuminate\Database\Eloquent\Model;

class VillaListing extends Model
{
    use HasVideoField;

    protected $fillable = [
        'slug', 'name', 'location', 'location_desc',
        'beds', 'guests', 'description', 'image', 'gallery', 'video', 'status',
    ];

    protected $casts = [
        'gallery' => 'array',
    ];

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
