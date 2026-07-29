<x-emails.layout :title="'Confirme sua inscrição na newsletter'">
    <p>Olá,</p>
    <p>Recebemos sua solicitação de inscrição na newsletter do {{ config('app.name') }}. Clique no botão abaixo para confirmar:</p>

    <p style="text-align: center;">
        <a class="button" href="{{ $confirmationUrl }}">Confirmar inscrição</a>
    </p>

    <p>Se o botão não funcionar, copie e cole este link no seu navegador:</p>
    <p><a href="{{ $confirmationUrl }}">{{ $confirmationUrl }}</a></p>

    <div class="meta">
        Se você não se inscreveu, ignore este e-mail ou cancele pelo link abaixo.
    </div>

    <p style="text-align: center;">
        <a href="{{ $unsubscribeUrl }}" style="font-size: 12px;">Cancelar inscrição</a>
    </p>
</x-emails.layout>
