<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RemarketingVoucher extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly object $order,
        public readonly string $voucherCode,
        public readonly int    $discountPercent = 10,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'LuxNest nhớ bạn — Quà tặng đặc biệt dành cho lần quay lại 🎁',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.remarketing_voucher',
        );
    }
}
