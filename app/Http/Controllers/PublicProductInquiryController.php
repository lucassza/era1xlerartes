<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductInquiryRequest;
use App\Models\Product;
use App\Services\CatalogCacheService;
use App\Services\InquiryNotificationService;
use App\Services\ProductInquiryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

class PublicProductInquiryController extends Controller
{
    public function __construct(
        private readonly ProductInquiryService $inquiries,
        private readonly CatalogCacheService $catalogCache,
    ) {}

    public function store(StoreProductInquiryRequest $request, string $productSlug): RedirectResponse|JsonResponse
    {
        $settings = $this->catalogCache->settings();

        $rateKey = 'product-inquiry:'.md5((string) $request->ip());
        if (RateLimiter::tooManyAttempts($rateKey, 5)) {
            $retry = RateLimiter::availableIn($rateKey);

            return back()
                ->withErrors(['rate' => 'Você excedeu o limite de envios. Tente novamente em '.ceil($retry / 60).' minuto(s).'])
                ->withInput();
        }

        $product = Product::query()
            ->where('slug', $productSlug)
            ->first();

        if (! $product instanceof Product) {
            abort(404);
        }

        $isPubliclyVisible = Product::query()
            ->publiclyVisible((bool) $settings->getAttribute('show_unavailable'))
            ->where('slug', $productSlug)
            ->exists();

        if (! $isPubliclyVisible) {
            abort(404);
        }

        $validated = $request->validated();
        $validated['product_id'] = $product->getKey();
        $validated['source_url'] = $request->fullUrl();
        $validated['ip'] = $request->ip();
        $validated['user_agent'] = $request->userAgent();
        $validated['utm'] = $request->only(['utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content']);

        $inquiry = $this->inquiries->store($validated);

        RateLimiter::hit($rateKey, 600);

        try {
            InquiryNotificationService::dispatch($inquiry);
        } catch (\Throwable $e) {
            Log::warning('Falha ao despachar notificação de nova consulta', [
                'inquiry_id' => $inquiry->getKey(),
                'error' => $e->getMessage(),
            ]);
        }

        $confirmation = (string) $settings->getAttribute('inquiry_confirmation_message')
            ?: 'Sua mensagem foi registrada. Em breve entraremos em contato.';

        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'ok',
                'message' => $confirmation,
                'inquiry_id' => $inquiry->getKey(),
            ], 201);
        }

        return back()
            ->with('inquiry_status', 'ok')
            ->with('inquiry_message', $confirmation);
    }
}
