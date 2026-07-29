<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\PostComment;
use App\Models\Testimonial;
use App\Models\User;
use App\Observers\PostCommentObserver;
use App\Observers\TestimonialObserver;
use App\Observers\UserObserver;
use App\Services\ProfanityFilter;
use Illuminate\Support\ServiceProvider;

class EngagementServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ProfanityFilter::class, function (): ProfanityFilter {
            return new ProfanityFilter(
                enabled: (bool) config('profanity.enabled', true),
                configuredTerms: (array) config('profanity.terms', []),
                replacement: (string) config('profanity.replacement_character', '*'),
            );
        });
    }

    public function boot(): void
    {
        User::observe(UserObserver::class);
        Testimonial::observe(TestimonialObserver::class);
        PostComment::observe(PostCommentObserver::class);
    }
}
