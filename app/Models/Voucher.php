<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    protected $fillable = [
        'code', 'name', 'discount_type', 'discount_value',
        'min_order_amount', 'max_uses', 'expires_at', 'is_active', 'notes',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'expires_at' => 'date',
    ];

    public function getDiscountLabelAttribute(): string
    {
        return $this->discount_type === 'percent'
            ? $this->discount_value . '%'
            : number_format($this->discount_value) . ' VNĐ';
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }
}
