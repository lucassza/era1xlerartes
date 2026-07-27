<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\BlogSetting;
use App\Models\NewsletterSubscriber;
use App\Services\NewsletterService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NewsletterController extends Controller
{
    public function subscribe(Request $request, NewsletterService $newsletterService): RedirectResponse
    {
        $settings = BlogSetting::query()->firstOrCreate([], [
            'site_name' => config('app.name', 'TDB Mix Blog'),
            'site_description' => 'Blog moderno em Laravel.',
            'default_meta_title' => config('app.name', 'TDB Mix Blog'),
            'default_meta_description' => 'Blog moderno em Laravel.',
            'newsletter_enabled' => true,
        ]);

        if (! $settings->newsletter_enabled) {
            return back()->with('newsletter_status', 'A newsletter está desabilitada no momento.');
        }

        $data = $request->validate([
            'email' => ['required', 'email:rfc,dns'],
        ]);

        $newsletterService->subscribe($data['email']);

        return back()->with('newsletter_status', 'Enviamos um e-mail de confirmação para sua inscrição.');
    }

    public function confirm(Request $request, NewsletterSubscriber $subscriber, NewsletterService $newsletterService): View
    {
        abort_unless($request->hasValidSignature(), 403);
        abort_unless(hash_equals($subscriber->token, (string) $request->query('token')), 403);

        $newsletterService->confirm($subscriber);

        return view('newsletter.confirmed', [
            'meta' => [
                'title' => 'Inscrição confirmada',
                'description' => 'Sua inscrição na newsletter foi confirmada.',
                'robots' => 'noindex,nofollow',
            ],
        ]);
    }

    public function unsubscribe(Request $request, NewsletterSubscriber $subscriber, NewsletterService $newsletterService): View
    {
        abort_unless($request->hasValidSignature(), 403);
        abort_unless(hash_equals($subscriber->token, (string) $request->query('token')), 403);

        $newsletterService->unsubscribe($subscriber);

        return view('newsletter.unsubscribed', [
            'meta' => [
                'title' => 'Inscrição cancelada',
                'description' => 'Sua inscrição na newsletter foi cancelada.',
                'robots' => 'noindex,nofollow',
            ],
        ]);
    }
}
