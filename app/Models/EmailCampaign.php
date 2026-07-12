<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailCampaign extends Model
{
    protected $fillable = [
        'name', 'subject', 'greeting', 'body',
        'voucher_mode', 'voucher_id', 'auto_discount_percent',
        'conditions', 'status', 'send_at', 'sent_at', 'sent_count',
    ];

    protected $casts = [
        'conditions' => 'array',
        'send_at'    => 'datetime',
        'sent_at'    => 'datetime',
    ];

    public function voucher()
    {
        return $this->belongsTo(Voucher::class);
    }
}
