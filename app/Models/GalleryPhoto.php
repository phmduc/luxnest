<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GalleryPhoto extends Model
{
    protected $fillable = ['image', 'caption', 'sort_order', 'is_active'];
    protected $casts    = ['is_active' => 'boolean'];
}
