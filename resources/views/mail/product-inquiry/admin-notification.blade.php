@component('mail::message')
# Nova consulta recebida

**Produto:** {{ $inquiry->product_name }}

**Interessado:** {{ $inquiry->name }}
**E-mail:** {{ $inquiry->email }}
@if (filled($inquiry->phone))
**Telefone/WhatsApp:** {{ $inquiry->phone }}
@endif
@if (filled($inquiry->company))
**Empresa:** {{ $inquiry->company }}
@endif
@if (filled($inquiry->city) || filled($inquiry->state))
**Localização:** {{ trim(($inquiry->city ?? '') . ' / ' . ($inquiry->state ?? ''), " /") }}
@endif

@if (filled($inquiry->subject))
**Assunto:** {{ $inquiry->subject }}
@endif

---

{{ $inquiry->message }}

---

Recebida em {{ $inquiry->created_at?->format('d/m/Y H:i') ?? '' }}.

@component('mail::button', ['url' => $product ? url('/admin/product-inquiries/' . $inquiry->id) : url('/admin/product-inquiries') ])
Abrir no painel
@endcomponent

@endcomponent
