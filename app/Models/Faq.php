<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
    protected $fillable = [
        'group_name', 'question', 'answer', 'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];
}
