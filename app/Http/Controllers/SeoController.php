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
}
