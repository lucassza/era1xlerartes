<x-emails.layout :title="'Novo depoimento aguardando aprovação'">
    <p>Olá, {{ $recipient->name }}!</p>

    <p>Um novo depoimento foi enviado por <strong>{{ $testimonial->name }}</strong> ({{ $testimonial->email }}@if($testimonial->city) · {{ $testimonial->city }}@endif) e aguarda moderação.</p>

    <div class="meta">
        <p><strong>Nota:</strong> {{ str_repeat('★', $testimonial->score) }}{{ str_repeat('☆', 5 - $testimonial->score) }} ({{ $testimonial->score }}/5)</p>
        <p><strong>Conteúdo:</strong></p>
        <blockquote style="margin: 0; padding-left: 12px; border-left: 3px solid #e95d78; color: #666666;">
            {{ $testimonial->content }}
        </blockquote>
    </div>

    <p style="text-align: center;">
        <a class="button" href="{{ $approveUrl }}">Revisar depoimento</a>
    </p>

    <p style="font-size: 12px; color: #666666;">
        IP de origem: {{ $testimonial->ip ?? 'não registrado' }} ·
        Enviado em {{ $testimonial->created_at?->format('d/m/Y H:i') ?? 'agora' }}
    </p>
</x-emails.layout>
