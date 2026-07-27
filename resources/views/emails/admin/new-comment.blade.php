<x-emails.layout :title="'Novo comentário aguardando aprovação'">
    <p>Olá, {{ $recipient->name }}!</p>

    <p>Um novo comentário foi enviado por <strong>{{ $comment->name }}</strong> ({{ $comment->email }}) em <strong>{{ $comment->post?->title ?? 'post removido' }}</strong> e aguarda moderação.</p>

    <div class="meta">
        <p><strong>Nota:</strong> {{ str_repeat('★', $comment->score) }}{{ str_repeat('☆', 5 - $comment->score) }} ({{ $comment->score }}/5)</p>
        <p><strong>Conteúdo:</strong></p>
        <blockquote style="margin: 0; padding-left: 12px; border-left: 3px solid #e6287e; color: #7a5555;">
            {{ $comment->content }}
        </blockquote>
    </div>

    <p style="text-align: center;">
        <a class="button" href="{{ $approveUrl }}">Revisar comentário</a>
    </p>

    <p style="font-size: 12px; color: #7a5555;">
        IP de origem: {{ $comment->ip ?? 'não registrado' }} ·
        Enviado em {{ $comment->created_at?->format('d/m/Y H:i') ?? 'agora' }}
    </p>
</x-emails.layout>
