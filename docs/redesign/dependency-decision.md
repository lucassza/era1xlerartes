# Decisão de dependências — Redesign TDB Mix

## Decisão

O redesign será implementado exclusivamente com as dependências já instaladas. Nenhuma dependência frontend, pacote de componentes, biblioteca de ícones ou plugin Alpine será adicionado nesta fase.

## Recursos aprovados

| Necessidade | Solução | Dependência existente |
| --- | --- | --- |
| Estilos, tokens e responsividade | Tailwind CSS 4, CSS-first com `@theme` | `tailwindcss` |
| Componentes visuais | Componentes Blade anônimos e utilities Tailwind | Laravel / Tailwind CSS |
| Tema claro e escuro | `data-theme`, `localStorage` e `matchMedia` | Navegador / Alpine.js |
| Menu, dropdown e busca | Alpine.js e elementos HTML semânticos | `alpinejs` |
| Modal | Elemento nativo `<dialog>` com Alpine.js | Navegador / Alpine.js |
| Ícones | SVG inline com texto alternativo ou rótulo acessível | HTML / Blade |
| Tipografia e assets | Pipeline Vite e Bunny Fonts configurado pelo Laravel Vite Plugin | Vite / `laravel-vite-plugin` |
| Logo | Asset estático local otimizado, quando disponibilizado | Vite / Blade |

## Alternativas descartadas

### Bootstrap ou kit de componentes

Descartados por contrariar o requisito de criar os componentes diretamente com Tailwind e Blade. Também introduziriam estilos e APIs sobrepostos ao design system próprio.

### Biblioteca de ícones

Descartada. Os poucos ícones necessários serão SVGs inline, permitindo controle de tamanho, contraste, `aria-hidden` e rótulos em controles somente com ícone.

### `@alpinejs/focus`

Descartado nesta fase. Menu e dropdown serão disclosures semânticos e modais usarão `<dialog>`, eliminando a necessidade de trap de foco por plugin. Uma futura adoção só será considerada se um fluxo exigir foco complexo que o elemento nativo não cubra.

### `fontaine`

Descartado. O build indicou que ele é opcional para fallback de fonte otimizado. Inter e Poppins já são entregues com `font-display: swap`; adicionar o pacote não é necessário para cumprir os objetivos atuais de performance.

## Logo da marca

A logo enviada pelo usuário deve ser utilizada no header e no footer. Ainda não há um asset de logo identificável e versionado no repositório. A implementação do shell usará o arquivo assim que ele estiver disponível em um caminho local estável, preferencialmente SVG ou WebP com transparência e dimensões explícitas.

## Regra para mudanças futuras

Qualquer nova dependência deve registrar necessidade, licença, manutenção, compatibilidade com Laravel 13 / Tailwind 4 / Alpine 3.15, impacto de bundle e estratégia de teste. A instalação só poderá ocorrer após aprovação explícita.
