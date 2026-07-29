<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\PostComment;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewCommentNotification extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public PostComment $comment,
        public User $recipient,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Novo comentário aguardando aprovação',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.admin.new-comment',
            with: [
                'comment' => $this->comment,
                'recipient' => $this->recipient,
                'approveUrl' => route('filament.admin.resources.post-comments.index', [
                    'tableFilters[post_id][value]' => $this->comment->post_id,
                    'tableFilters[status][value]' => 'pending',
                ]),
            ],
        );
    }
}
