<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BookingConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly object $order,
        public readonly object $item,
        public readonly ?object $room,
        public readonly string $qrImageUrl = '',
        public readonly string $qrCheckinUrl = '',
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Xác nhận đặt phòng #' . str_pad($this->order->id, 6, '0', STR_PAD_LEFT) . ' - LuxNest',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.booking_confirmation',
        );
    }
}
