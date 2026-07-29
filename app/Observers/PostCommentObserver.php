<?php

declare(strict_types=1);

namespace App\Observers;

use App\Enums\PostCommentStatus;
use App\Mail\NewCommentNotification;
use App\Models\PostComment;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class PostCommentObserver
{
    public function created(PostComment $comment): void
    {
        if ($comment->status !== PostCommentStatus::Pending) {
            return;
        }

        $recipients = User::query()
            ->whereNull('banned_at')
            ->where(function ($query): void {
                $query->where('type', 'admin')
                    ->where(function ($q): void {
                        $q->whereHas('roles.permissions', fn ($permission) => $permission->where('name', 'comments.approve'));
                    });
            })
            ->get();

        foreach ($recipients as $recipient) {
            Mail::to($recipient->email)->queue(new NewCommentNotification($comment, $recipient));
        }
    }
}
