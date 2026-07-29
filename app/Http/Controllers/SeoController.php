<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\SeoService;
use Illuminate\Http\Response;

class SeoController extends Controller
{
    public function sitemap(SeoService $seoService): Response
    {
        return response()->view('seo.sitemap', [
            'urls' => $seoService->sitemapUrls(),
        ], 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
        ]);
    }

    public function rss(SeoService $seoService): Response
    {
        return response()->view('seo.rss', [
            'channel' => $seoService->rssChannel(),
            'items' => $seoService->rssItems(),
        ], 200, [
            'Content-Type' => 'application/rss+xml; charset=UTF-8',
        ]);
    }

    public function robots(): Response
    {
        $sitemap = route('seo.sitemap');
        $body = "User-agent: *\nAllow: /\nDisallow: /admin\nDisallow: /depoimentos\nDisallow: /perfil\nDisallow: /dashboard\n\nSitemap: {$sitemap}\n";

        return response($body, 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
    }
}
