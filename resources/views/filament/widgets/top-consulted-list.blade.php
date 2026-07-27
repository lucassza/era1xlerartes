@php
    /** @var array<int, array{name: string, total: int, url: ?string}> $rows */
@endphp
<div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-white/5">
    @if (empty($rows))
        <p class="text-sm text-gray-500 dark:text-gray-400">
            Sem consultas registradas ainda.
        </p>
    @else
        <ol class="space-y-2">
            @foreach ($rows as $row)
                <li class="flex items-center justify-between gap-2 text-sm">
                    <span class="truncate">
                        @if (! empty($row['url']))
                            <a href="{{ $row['url'] }}" class="font-medium text-primary-600 hover:underline">
                                {{ $row['name'] }}
                            </a>
                        @else
                            <span class="font-medium text-gray-700 dark:text-gray-200">{{ $row['name'] }}</span>
                        @endif
                    </span>
                    <span class="rounded-full bg-primary-100 px-2 py-0.5 text-xs font-semibold text-primary-700 dark:bg-primary-900/40 dark:text-primary-200">
                        {{ $row['total'] }}
                    </span>
                </li>
            @endforeach
        </ol>
    @endif
</div>
