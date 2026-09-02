@php
    /** @var \App\Models\Product|null $product */
    $productId = $product?->getKey() ?? request()->input('product_id');
    $productName = $product?->name ?? request()->input('product_name');
    $buttonText = $settings->inquiry_button_text ?? 'Consulte';
@endphp

<div
    x-data="productInquiryModal()"
    x-on:keyup.escape.window="close()"
    class="relative z-50"
>
    <div
        x-show="open"
        x-cloak
        class="fixed inset-0 flex items-center justify-center bg-black/60 p-4"
        role="dialog"
        aria-modal="true"
        aria-labelledby="inquiry-title"
    >
        <div
            @click.outside="close()"
            class="w-full max-w-lg overflow-hidden rounded-card border border-line bg-surface-raised shadow-card"
        >
            <header class="flex items-start justify-between gap-2 border-b border-line p-4">
                <div>
                    <h2 id="inquiry-title" class="text-lg font-semibold text-foreground">{{ $buttonText }}</h2>
                    @if (filled($productName))
                        <p class="mt-1 text-sm text-muted">Sobre <strong class="text-foreground">{{ $productName }}</strong></p>
                    @endif
                </div>
                <button type="button" @click="close()" class="rounded-control p-2 text-muted hover:bg-surface hover:text-foreground" aria-label="Fechar">
                    <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5" class="size-5"><path d="M5 5l10 10M15 5L5 15" stroke-linecap="round"/></svg>
                </button>
            </header>

            @if (session('inquiry_status') === 'ok')
                <div class="space-y-4 p-6" role="alert">
                    <div class="rounded-card bg-success-surface p-4 text-sm">
                        <p class="font-bold text-success-text">Mensagem enviada.</p>
                        <p class="mt-1 text-success-muted">{{ session('inquiry_message') ?? 'Sua mensagem foi registrada. Em breve entraremos em contato.' }}</p>
                    </div>
                    <button type="button" @click="close()" class="inline-flex h-10 items-center justify-center rounded-control bg-primary px-4 text-sm font-semibold text-white">Fechar</button>
                </div>
            @else
                <form
                    method="POST"
                    x-bind:action="action"
                    class="space-y-4 p-6"
                >
                    @csrf
                    <input type="hidden" name="website" value="" tabindex="-1" autocomplete="off">
                    <input type="hidden" name="product_id" x-bind:value="productId">

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <label class="block text-sm">
                            <span class="mb-1 block font-medium text-foreground">Nome completo *</span>
                            <input type="text" name="name" required minlength="2" maxlength="120" value="{{ old('name') }}" class="h-10 w-full rounded-control border border-line bg-surface px-3 text-sm focus:outline-none focus:ring-2 focus:ring-secondary" />
                            @error('name')<span class="mt-1 block text-xs text-danger">{{ $message }}</span>@enderror
                        </label>
                        <label class="block text-sm">
                            <span class="mb-1 block font-medium text-foreground">E-mail *</span>
                            <input type="email" name="email" required maxlength="255" value="{{ old('email') }}" class="h-10 w-full rounded-control border border-line bg-surface px-3 text-sm focus:outline-none focus:ring-2 focus:ring-secondary" />
                            @error('email')<span class="mt-1 block text-xs text-danger">{{ $message }}</span>@enderror
                        </label>
                        <label class="block text-sm">
                            <span class="mb-1 block font-medium text-foreground">Telefone/WhatsApp</span>
                            <input type="tel" name="phone" maxlength="30" value="{{ old('phone') }}" class="h-10 w-full rounded-control border border-line bg-surface px-3 text-sm focus:outline-none focus:ring-2 focus:ring-secondary" />
                            @error('phone')<span class="mt-1 block text-xs text-danger">{{ $message }}</span>@enderror
                        </label>
                        <label class="block text-sm">
                            <span class="mb-1 block font-medium text-foreground">Empresa</span>
                            <input type="text" name="company" maxlength="160" value="{{ old('company') }}" class="h-10 w-full rounded-control border border-line bg-surface px-3 text-sm focus:outline-none focus:ring-2 focus:ring-secondary" />
                            @error('company')<span class="mt-1 block text-xs text-danger">{{ $message }}</span>@enderror
                        </label>
                        <label class="block text-sm">
                            <span class="mb-1 block font-medium text-foreground">Cidade</span>
                            <input type="text" name="city" maxlength="120" value="{{ old('city') }}" class="h-10 w-full rounded-control border border-line bg-surface px-3 text-sm focus:outline-none focus:ring-2 focus:ring-secondary" />
                        </label>
                        <label class="block text-sm">
                            <span class="mb-1 block font-medium text-foreground">UF</span>
                            <input type="text" name="state" maxlength="2" minlength="2" value="{{ old('state') }}" placeholder="SP" class="h-10 w-full rounded-control border border-line bg-surface px-3 text-sm uppercase focus:outline-none focus:ring-2 focus:ring-secondary" />
                        </label>
                    </div>

                    <label class="block text-sm">
                        <span class="mb-1 block font-medium text-foreground">Assunto</span>
                        <input type="text" name="subject" maxlength="160" value="{{ old('subject') ?? ($productName ? 'Interesse em '.$productName : null) }}" class="h-10 w-full rounded-control border border-line bg-surface px-3 text-sm focus:outline-none focus:ring-2 focus:ring-secondary" />
                    </label>

                    <label class="block text-sm">
                        <span class="mb-1 block font-medium text-foreground">Mensagem *</span>
                        <textarea name="message" required minlength="10" maxlength="2000" rows="4" class="w-full rounded-control border border-line bg-surface px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-secondary">{{ old('message') }}</textarea>
                        @error('message')<span class="mt-1 block text-xs text-danger">{{ $message }}</span>@enderror
                    </label>

                    <label class="flex items-start gap-2 text-xs text-muted">
                        <input type="checkbox" name="consent" required value="1" @checked(old('consent')) class="mt-0.5 size-4 rounded border-line text-primary focus:ring-secondary" />
                        <span>Li e aceito receber contato comercial referente a esta consulta. Os dados serão tratados conforme a <a href="#" class="text-primary underline">política de privacidade</a> (LGPD).</span>
                    </label>

                    @error('rate')
                        <p class="text-sm text-danger">{{ $message }}</p>
                    @enderror

                    <div class="flex flex-col-reverse gap-2 pt-2 sm:flex-row sm:items-center sm:justify-end">
                        <button type="button" @click="close()" class="inline-flex h-10 items-center justify-center rounded-control border border-line bg-surface px-4 text-sm font-semibold text-foreground">Cancelar</button>
                        <button type="submit" class="inline-flex h-10 items-center justify-center rounded-control bg-primary px-4 text-sm font-semibold text-white hover:bg-primary-strong">
                            Enviar consulta
                        </button>
                    </div>
                </form>
            @endif
        </div>
    </div>
</div>

@once
    @push('scripts')
        <script>
            function productInquiryModal() {
                return {
                    open: false,
                    productId: null,
                    productName: null,
                    action: '',
                    show(productId, productSlug, productName) {
                        if (productId) this.productId = productId;
                        if (productName) this.productName = productName;
                        this.action = productSlug ? `/produtos/${productSlug}/consulta` : '';
                        this.open = true;
                    },
                    close() {
                        this.open = false;
                    },
                };
            }
            document.addEventListener('click', function (e) {
                const target = e.target instanceof Element ? e.target.closest('[data-open-inquiry]') : null;
                if (! target) return;
                e.preventDefault();
                const productId = target.getAttribute('data-product-id');
                const productSlug = target.getAttribute('data-product-slug');
                const productName = target.getAttribute('data-product-name');
                const root = document.querySelector('[x-data^="productInquiryModal"]');
                if (! root || ! root._x_dataStack) return;
                const data = root._x_dataStack[0];
                data.show(productId, productSlug, productName);
            });
        </script>
    @endpush
@endonce
