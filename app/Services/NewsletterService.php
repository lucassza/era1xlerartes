<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\NewsletterSubscriberStatus;
use App\Mail\NewsletterConfirmationMail;
use App\Models\NewsletterSubscriber;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

class NewsletterService
{
    public function subscribe(string $email): NewsletterSubscriber
    {
        $subscriber = NewsletterSubscriber::query()->firstOrNew(['email' => $email]);

        $subscriber->status = NewsletterSubscriberStatus::Pending;
        $subscriber->confirmed_at = null;
        $subscriber->unsubscribed_at = null;
        $subscriber->save();

        Mail::to($subscriber->email)->queue(new NewsletterConfirmationMail(
            subscriber: $subscriber,
            confirmationUrl: $this->confirmationUrl($subscriber),
            unsubscribeUrl: $this->unsubscribeUrl($subscriber),
        ));

        return $subscriber;
    }

    public function confirm(NewsletterSubscriber $subscriber): NewsletterSubscriber
    {
        $subscriber->status = NewsletterSubscriberStatus::Confirmed;
        $subscriber->confirmed_at = now();
        $subscriber->unsubscribed_at = null;
        $subscriber->save();

        return $subscriber;
    }

    public function unsubscribe(NewsletterSubscriber $subscriber): NewsletterSubscriber
    {
        $subscriber->status = NewsletterSubscriberStatus::Unsubscribed;
        $subscriber->unsubscribed_at = now();
        $subscriber->save();

        return $subscriber;
    }

    public function confirmationUrl(NewsletterSubscriber $subscriber): string
    {
        return URL::temporarySignedRoute(
            'newsletter.confirm',
            now()->addDays(7),
            ['subscriber' => $subscriber->getKey(), 'token' => $subscriber->token],
        );
    }

    public function unsubscribeUrl(NewsletterSubscriber $subscriber): string
    {
        return URL::temporarySignedRoute(
            'newsletter.unsubscribe',
            now()->addDays(30),
            ['subscriber' => $subscriber->getKey(), 'token' => $subscriber->token],
        );
    }
}
