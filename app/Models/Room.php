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
        return match ($this->branch) {
            'Villa'     => '#7c3aed',
            'Residence' => '#0f766e',
            default     => '#1a3a6b',
        };
    }
}
