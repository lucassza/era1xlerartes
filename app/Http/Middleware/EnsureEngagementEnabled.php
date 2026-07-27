<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\BlogSetting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureEngagementEnabled
{
    public function handle(Request $request, Closure $next, string $feature): Response
    {
        $setting = BlogSetting::query()->first();

        $flag = match ($feature) {
            'testimonials' => $setting->testimonials_enabled ?? true,
            'comments' => $setting->comments_enabled ?? true,
            default => true,
        };

        if (! $flag) {
            abort(404);
        }

        return $next($request);
    }
}
