<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\HtmlSanitizer;
use Illuminate\Support\Str;

class News extends Model
{
    protected $table = 'news';

    protected $fillable = [
        'title', 'slug', 'excerpt', 'content', 'tag', 'image', 'published_at', 'status',
    ];

    protected $casts = [
        'published_at' => 'date',
    ];

    /** Nội dung đã lọc, sẵn sàng in ra trang. Bài cũ dạng text thuần vẫn giữ xuống dòng. */
    public function getContentHtmlAttribute(): string
    {
        return HtmlSanitizer::render($this->content);
    }

    /** Build a slug that no other article is using. */
    public static function uniqueSlug(?string $slug, string $title, ?int $excludeId = null): string
    {
        $base = Str::slug($slug ?: $title) ?: 'bai-viet';

        $candidate = $base;
        $i = 2;
        while (
            static::where('slug', $candidate)
                ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
                ->exists()
        ) {
            $candidate = $base . '-' . $i++;
        }

        return $candidate;
    }
}
