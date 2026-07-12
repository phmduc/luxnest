<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = [
        'site_name', 'logo', 'og_image', 'hotline', 'email', 'address', 'map_link',
        'facebook_url', 'instagram_url', 'youtube_url', 'footer_description',
        'remarketing_subject', 'remarketing_greeting', 'remarketing_body',
        'remarketing_discount', 'remarketing_auto', 'remarketing_send_at',
    ];

    protected $casts = [
        'remarketing_auto'    => 'boolean',
        'remarketing_send_at' => 'datetime',
    ];

    public static function current(): self
    {
        return Cache::rememberForever('site_settings', fn () => self::firstOrCreate(['id' => 1]));
    }

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget('site_settings'));
    }
}
