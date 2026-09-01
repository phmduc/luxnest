<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    protected $fillable = ['name', 'label', 'color', 'sort_order', 'is_active'];

    protected $casts = [
        'is_active'  => 'boolean',
        'sort_order' => 'integer',
    ];

    public function rooms()
    {
        return $this->hasMany(Room::class, 'branch', 'name');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    /** @return \Illuminate\Support\Collection<int, static> */
    public static function active()
    {
        return static::where('is_active', true)->ordered()->get();
    }

    /** Màu nhãn của chi nhánh; nạp một lần cho mỗi request. */
    public static function colorFor(?string $name): string
    {
        static $colors = null;

        $colors ??= static::pluck('color', 'name')->all();

        return $colors[$name] ?? '#1a3a6b';
    }

    /** Tên chi nhánh hợp lệ, dùng cho validate và cho bộ lọc. */
    public static function names(): array
    {
        return static::ordered()->pluck('name')->all();
    }
}
