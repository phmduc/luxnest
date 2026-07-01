<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class News extends Model
{
    protected $table = 'news';

    protected $fillable = [
        'title', 'slug', 'excerpt', 'content', 'tag', 'image', 'published_at', 'status',
    ];

    protected $casts = [
        'published_at' => 'date',
    ];
}
