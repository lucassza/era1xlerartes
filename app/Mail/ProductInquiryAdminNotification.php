<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\ProductInquiry;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ProductInquiryAdminNotification extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(public readonly ProductInquiry $inquiry) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: config('mail.from.address') ? new Address((string) config('mail.from.address'), (string) config('mail.from.name')) : null,
            subject: '[Catálogo] Nova consulta: '.$this->inquiry->getAttribute('product_name'),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.product-inquiry.admin-notification',
            with: [
                'inquiry' => $this->inquiry,
                'product' => $this->inquiry->product,
            ],
        );
    }
}
