<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\PostSearchService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class PublicSearchController extends Controller
{
    public function index(Request $request, PostSearchService $searchService): View
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:255'],
        ]);

        $query = trim((string) ($validated['q'] ?? ''));
        $results = $query !== ''
            ? $searchService->searchPublished($query, 12)
            : collect();

        return view('search.index', [
            'query' => $query,
            'results' => $results,
            'meta' => [
                'title' => $query !== '' ? sprintf('Busca por "%s" | %s', $query, config('app.name')) : sprintf('Buscar | %s', config('app.name')),
                'description' => $query !== '' ? sprintf('Resultados de busca por "%s".', $query) : 'Busque posts publicados no blog.',
                'canonical' => $query !== '' ? route('search.index', ['q' => $query]) : route('search.index'),
                'robots' => 'noindex,follow',
            ],
        ]);
    }
}
