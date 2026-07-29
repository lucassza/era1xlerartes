@php
    $headingCounts = [];
    $headings = [];
    $articleContent = preg_replace_callback('/<h([2-3])([^>]*)>(.*?)<\/h\1>/is', function (array $match) use (&$headingCounts, &$headings): string {
        $text = trim(html_entity_decode(strip_tags($match[3]), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        $existingId = preg_match('/\bid\s*=\s*["\']([^"\']+)["\']/i', $match[2], $idMatch) ? $idMatch[1] : null;
        $baseId = \Illuminate\Support\Str::slug($existingId ?: $text);
        $baseId = $baseId !== '' ? $baseId : 'secao';
        $headingCounts[$baseId] = ($headingCounts[$baseId] ?? 0) + 1;
        $id = $headingCounts[$baseId] > 1 ? "{$baseId}-{$headingCounts[$baseId]}" : $baseId;

        $headings[] = ['id' => $id, 'text' => $text, 'level' => (int) $match[1]];

        $attributes = preg_replace('/\s\bid\s*=\s*["\'][^"\']+["\']/i', '', $match[2]) ?? $match[2];

        return "<h{$match[1]} {$attributes} id=\"{$id}\">{$match[3]}</h{$match[1]}>";
    }, $post->content) ?? $post->content;
    $readingTime = $post->readingTime();
    $cover = $post->getFirstMedia('cover');
    $coverUrl = $cover?->hasGeneratedConversion('thumb-1200') ? $cover->getUrl('thumb-1200') : $cover?->getUrl();
    $coverSrcset = $cover?->hasResponsiveImages('thumb-1200') ? $cover->getSrcset('thumb-1200') : null;
    $postRatingService = app(\App\Services\PostRatingAggregator::class);
    $postAverage = $postRatingService->average($post);
    $postRatingsCount = $postRatingService->count($post);
    $postDistribution = $postRatingService->distribution($post);
    $approvedComments = $post->approvedComments()->latest('approved_at')->get();
@endphp

<x-layouts.app :meta="$meta" :json-ld="$jsonLd">
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 sm:py-12 lg:px-8">
        <x-article.breadcrumb :post="$post" />

        <article class="mx-auto max-w-4xl">
            <header class="border-b border-line pb-8 sm:pb-10">
                <x-article.meta :post="$post" :reading-time="$readingTime" />

                <h1 class="mt-5 font-display text-3xl font-bold tracking-tight text-foreground sm:text-4xl lg:text-5xl">
                    {{ $post->title }}
                </h1>

                @if (filled($post->excerpt))
                    <p class="mt-5 max-w-3xl text-lg leading-8 text-muted sm:text-xl">
                        {{ $post->excerpt }}
                    </p>
                @endif
            </header>

            @if (filled($coverUrl))
                <figure class="mt-8 overflow-hidden rounded-feature border border-line bg-surface shadow-card sm:mt-10">
                    <div class="aspect-video">
                        <img src="{{ $coverUrl }}" @if ($coverSrcset) srcset="{{ $coverSrcset }}" sizes="(min-width: 1280px) 896px, (min-width: 1024px) calc(100vw - 20rem), 100vw" @endif alt="Capa do artigo: {{ $post->title }}" width="1200" height="675" loading="eager" fetchpriority="high" class="size-full object-cover">
                    </div>
                </figure>
            @endif

            <div class="mt-8 grid gap-8 lg:mt-10 lg:grid-cols-[minmax(0,1fr)_15rem] lg:items-start">
                <div class="min-w-0">
                    <x-article.toc :headings="$headings" />

                    <div class="article-content mt-8 text-[1.0625rem] leading-8 text-foreground [&_a]:font-semibold [&_a]:text-primary [&_a]:underline [&_a]:decoration-primary/50 [&_a]:underline-offset-4 hover:[&_a]:text-secondary [&_blockquote]:my-8 [&_blockquote]:border-l-4 [&_blockquote]:border-primary [&_blockquote]:bg-surface [&_blockquote]:px-5 [&_blockquote]:py-4 [&_blockquote]:text-lg [&_blockquote]:italic [&_code]:rounded [&_code]:bg-surface [&_code]:px-1.5 [&_code]:py-0.5 [&_code]:font-mono [&_code]:text-[0.9em] [&_h2]:mt-12 [&_h2]:scroll-mt-8 [&_h2]:font-display [&_h2]:text-2xl [&_h2]:font-bold [&_h2]:leading-tight sm:[&_h2]:text-3xl [&_h3]:mt-8 [&_h3]:scroll-mt-8 [&_h3]:font-display [&_h3]:text-xl [&_h3]:font-bold [&_li]:my-2 [&_ol]:my-6 [&_ol]:list-decimal [&_ol]:pl-6 [&_p]:my-6 [&_pre]:my-8 [&_pre]:overflow-x-auto [&_pre]:rounded-card [&_pre]:bg-slate-950 [&_pre]:p-5 [&_pre]:text-sm [&_pre]:leading-6 [&_pre]:text-slate-100 [&_pre_code]:bg-transparent [&_pre_code]:p-0 [&_ul]:my-6 [&_ul]:list-disc [&_ul]:pl-6">
                        {!! $articleContent !!}
                    </div>

                    <x-article.share :post="$post" />
                </div>

                <aside class="hidden lg:block lg:sticky lg:top-6">
                    <x-article.toc :headings="$headings" desktop />
                </aside>
            </div>

            <x-article.author-card :author="$post->author" />

            <x-article.rating-summary :average="$postAverage" :count="$postRatingsCount" :distribution="$postDistribution" />

            <x-article.comments :post="$post" :comments="$approvedComments" />

            <x-article.related-posts heading="Posts relacionados" :posts="$relatedPosts" />
            <x-article.related-posts heading="Posts populares" :posts="$popularPosts" show-views />
        </article>
    </div>
</x-layouts.app>
