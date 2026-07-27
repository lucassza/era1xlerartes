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

class ProductInquiryVisitorConfirmation extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(public readonly ProductInquiry $inquiry) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            to: [new Address((string) $this->inquiry->getAttribute('email'), (string) $this->inquiry->getAttribute('name'))],
            replyTo: [new Address((string) config('mail.from.address'))],
            subject: 'Recebemos sua consulta sobre '.$this->inquiry->getAttribute('product_name'),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.product-inquiry.visitor-confirmation',
            with: [
                'inquiry' => $this->inquiry,
                'product' => $this->inquiry->product,
            ],
        );
    }
}
