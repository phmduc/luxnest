<?php

namespace App\Models;

use App\Traits\HasVideoField;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasVideoField;

    protected $fillable = [
        'wp_id', 'slug', 'name', 'branch', 'type',
        'description', 'price', 'regular_price',
        'image', 'gallery', 'video', 'amenities', 'status', 'gohost_room_type_id',
    ];

    protected $casts = [
        'amenities'     => 'array',
        'gallery'       => 'array',
        'price'         => 'integer',
        'regular_price' => 'integer',
    ];

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function getBranchColorAttribute(): string
    {
        return Branch::colorFor($this->branch);
    }
}
