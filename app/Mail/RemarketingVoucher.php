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
        public readonly string $voucherCode = '',
        public readonly int    $discountPercent = 10,
        public readonly string $greeting = '',
        public readonly string $body = '',
        public readonly string $hotline = '',
        public readonly string $customSubject = '',
        public readonly string $voucherMode = 'auto', // none|fixed|auto
    ) {}

    public function envelope(): Envelope
    {
        $subject = $this->customSubject
            ?: 'LuxNest nhớ bạn — Quà tặng đặc biệt dành cho lần quay lại 🎁';

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.remarketing_voucher');
    }
}
