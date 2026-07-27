<x-filament-panels::page>
    @php
        $eventLabels = [
            'created' => ['label' => 'Criado',      'color' => '#2fb344'],
            'updated' => ['label' => 'Atualizado',  'color' => '#0ea5e9'],
            'deleted' => ['label' => 'Deletado',    'color' => '#d90429'],
        ];

        $modelLabels = [
            'User'                    => 'Usuário',
            'Post'                    => 'Post',
            'Category'                => 'Categoria',
            'Tag'                     => 'Tag',
            'Author'                  => 'Autor',
            'NewsletterSubscriber'    => 'Newsletter',
            'BlogSetting'             => 'Configuração',
            'Role'                    => 'Papel',
            'Permission'              => 'Permissão',
        ];
    @endphp

    <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white">
        <table class="min-w-full divide-y divide-zinc-200 text-sm">
            <thead class="bg-zinc-50">
                <tr>
                    <th class="px-4 py-3 text-left font-medium text-zinc-600 w-40">Data</th>
                    <th class="px-4 py-3 text-left font-medium text-zinc-600 w-28">Ação</th>
                    <th class="px-4 py-3 text-left font-medium text-zinc-600">Quem fez</th>
                    <th class="px-4 py-3 text-left font-medium text-zinc-600">Modelo</th>
                    <th class="px-4 py-3 text-left font-medium text-zinc-600">Registro</th>
                    <th class="px-4 py-3 text-left font-medium text-zinc-600">Detalhes</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-200">
                @forelse ($this->activities as $activity)
                    @php
                        $event     = $activity->event ?? $activity->description ?? '';
                        $eventInfo = $eventLabels[$event] ?? ['label' => ucfirst($event), 'color' => '#7a5555'];

                        // Quem fez a ação
                        $causer = $activity->causer;
                        if ($causer) {
                            $causerName = $causer->name ?? $causer->email ?? ('ID ' . $activity->causer_id);
                        } else {
                            $causerName = null;
                        }

                        // Tipo do modelo afetado
                        $subjectClass = $activity->subject_type ? class_basename($activity->subject_type) : null;
                        $subjectLabel = $subjectClass ? ($modelLabels[$subjectClass] ?? $subjectClass) : '—';

                        // Identificador do registro afetado
                        $subject = $activity->subject;
                        if ($subject) {
                            $subjectName = $subject->name ?? $subject->title ?? $subject->email ?? null;
                        } else {
                            // Tenta pegar dos properties se o registro foi deletado
                            $props = $activity->properties->toArray();
                            $old   = $props['old'] ?? $props['attributes'] ?? [];
                            $subjectName = $old['name'] ?? $old['title'] ?? $old['email'] ?? null;
                        }

                        // Resumo das propriedades alteradas
                        $props   = $activity->properties->toArray();
                        $changed = [];
                        if (isset($props['old']) && isset($props['attributes'])) {
                            foreach ($props['attributes'] as $key => $newVal) {
                                $oldVal = $props['old'][$key] ?? null;
                                if ($oldVal !== $newVal && ! in_array($key, ['updated_at', 'remember_token'])) {
                                    $changed[] = $key;
                                }
                            }
                        } elseif (isset($props['attributes'])) {
                            $changed = array_keys(
                                array_diff_key(
                                    $props['attributes'],
                                    array_flip(['id', 'created_at', 'updated_at', 'remember_token', 'email_verified_at'])
                                )
                            );
                        }
                    @endphp
                    <tr class="hover:bg-zinc-50 transition-colors">
                        <td class="px-4 py-3 text-zinc-500 tabular-nums whitespace-nowrap">
                            {{ optional($activity->created_at)->format('d/m/Y H:i') }}
                        </td>
                        <td class="px-4 py-3">
                            <span style="
                                display: inline-flex;
                                align-items: center;
                                padding: 2px 8px;
                                border-radius: 9999px;
                                font-size: 0.75rem;
                                font-weight: 600;
                                color: {{ $eventInfo['color'] }};
                                background: color-mix(in srgb, {{ $eventInfo['color'] }} 12%, white);
                            ">
                                {{ $eventInfo['label'] }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            @if ($causerName)
                                <span class="font-medium text-zinc-800">{{ $causerName }}</span>
                            @else
                                <span class="text-zinc-400">Sistema</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-zinc-600">{{ $subjectLabel }}</td>
                        <td class="px-4 py-3">
                            @if ($subjectName)
                                <span class="font-medium text-zinc-800">{{ $subjectName }}</span>
                            @elseif ($activity->subject_id)
                                <span class="font-mono text-xs text-zinc-400">{{ substr($activity->subject_id, 0, 8) }}…</span>
                            @else
                                <span class="text-zinc-400">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if (count($changed) > 0)
                                <span class="text-xs text-zinc-500">
                                    {{ implode(', ', array_map(fn($k) => match($k) {
                                        'name'       => 'nome',
                                        'email'      => 'e-mail',
                                        'password'   => 'senha',
                                        'type'       => 'tipo',
                                        'status'     => 'status',
                                        'is_active'  => 'ativo',
                                        'bio'        => 'bio',
                                        'title'      => 'título',
                                        'content'    => 'conteúdo',
                                        'slug'       => 'slug',
                                        default      => $k,
                                    }, $changed)) }}
                                </span>
                            @elseif ($event === 'created')
                                <span class="text-xs text-zinc-400">Registro criado</span>
                            @else
                                <span class="text-zinc-400">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-zinc-400">
                            Nenhum registro de auditoria encontrado.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-filament-panels::page>
