<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\TestimonialStatus;
use App\Http\Requests\StoreTestimonialRequest;
use App\Models\BlogSetting;
use App\Models\Testimonial;
use App\Services\EngagementService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PublicTestimonialController extends Controller
{
    public function index(Request $request): View
    {
        $settings = BlogSetting::query()->first();

        $testimonials = Testimonial::query()
            ->approved()
            ->latest('approved_at')
            ->paginate(12)
            ->withQueryString();

        return view('testimonials.index', [
            'meta' => [
                'title' => 'Depoimentos — Era1x Ler Artes',
                'description' => 'Veja o que a nossa comunidade está dizendo sobre o blog.',
            ],
            'testimonials' => $testimonials,
            'testimonialsEnabled' => (bool) ($settings->testimonials_enabled ?? true),
        ]);
    }

    public function store(StoreTestimonialRequest $request, EngagementService $service): RedirectResponse
    {
        $testimonial = $service->submitTestimonial($request->user(), $request->validated());

        if ($testimonial->status === TestimonialStatus::Rejected) {
            return back()->with('engagement_status', 'Não foi possível enviar seu depoimento. Revise o conteúdo e tente novamente.');
        }

        return back()->with('engagement_status', 'Recebemos seu depoimento! Ele será publicado após aprovação do administrador.');
    }
}
