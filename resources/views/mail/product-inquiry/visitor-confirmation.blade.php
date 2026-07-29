@component('mail::message')
# Recebemos sua consulta

Olá **{{ $inquiry->name }}**,

Recebemos sua mensagem sobre **{{ $inquiry->product_name }}** e ela já foi encaminhada para nossa equipe comercial. Em breve você receberá um retorno pelos canais informados.

> {{ $inquiry->message }}

Este é um e-mail de confirmação automático. Caso não reconheça esta solicitação, ignore esta mensagem.

@endcomponent
