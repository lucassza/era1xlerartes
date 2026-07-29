# Design: Hero configurável e refinamento visual do catálogo

## Dados

### Migration `2026_07_28_120000_add_hero_to_catalog_settings`

Adiciona colunas à tabela `catalog_settings` (id incremental). Nenhuma
coluna é removida; `banner` (JSON legado) fica como legado sem leitura
no frontend até decisão futura de remoção.

```
hero_enabled        boolean default false
hero_title          varchar(255) nullable
hero_subtitle       text nullable
hero_cta_label      varchar(100) nullable
hero_cta_url        varchar(255) nullable
hero_image_path     varchar(255) nullable
hero_alignment      varchar(20) default 'left'
```

Não criamos nova tabela: a `catalog_settings` é singleton e `firstOrCreate`
em `CatalogSetting::current()` continua entregando 1 linha.

### Model `CatalogSetting`

Adiciona os novos campos a:

- `#[Fillable([...])]`
- `casts()`:
  - `hero_enabled` => `boolean`
  - (texto e url permanecem string)
- `defaults()`: `hero_enabled => false`, `hero_alignment => 'left'`.

## Painel — Filament

`ManageCatalogSettings` ganha nova `Section::make('Hero da home')` no topo
do formulário (antes de "Apresentação"), com:

- `Toggle::make('hero_enabled')` mestre, `inline(false)`, helper
  "Quando desativado, a home mostra apenas o título e o texto
  introdutório no padrão atual."
- `TextInput::make('hero_title')` (maxLength 255).
- `Textarea::make('hero_subtitle')` (rows 2).
- Em `Grid` 2 colunas: `TextInput::make('hero_cta_label')` (maxLength
  100) e `TextInput::make('hero_cta_url')` (url, maxLength 255).
- `FileUpload::make('hero_image_path')` em disco `public`, pasta
  `catalog/hero`, com `image()` (max 4 MB, recomendado 1920×800).
- `Select::make('hero_alignment')` com `['left' => 'Esquerda',
  'center' => 'Centro']`.

O `save()` permanece genérico: basta `fill($data)->save()`. O
`CatalogObserver` já existente invalida o cache de settings ao salvar.

## Componente público `<x-site.catalog-hero>`

`resources/views/components/site/catalog-hero.blade.php` recebe
`$settings` (model `CatalogSetting`). Retorna string vazia quando
`hero_enabled` é falso. Quando ativo:

- `<section>` com `aria-label="Destaque principal do catálogo"`.
- **Variante com imagem**: `<img>` absoluta (asset/storage) com
  `object-cover`, overlay `from-primary/80 via-primary/40 to-transparent`
  (linear, diagonal) sobre o conteúdo.
- **Variante sem imagem**: `bg-gradient-to-br from-primary to-secondary`
  sem `<img>`.
- Conteúdo: badge superior opcional, `<h1>` com `font-display` e
  tamanho responsivo (`text-3xl sm:text-4xl lg:text-5xl`), subtítulo
  com `max-w-prose`, CTA `<x-ui.button variant="primary" size="lg">`
  quando `hero_cta_label` e `hero_cta_url` estiverem presentes.
- Alinhamento: classe utilitária aplicada no container interno
  (`text-left` ou `text-center`, com `items-start` ou `items-center`).
- Responsivo: padding `px-6 py-12 sm:px-10 sm:py-16 lg:py-20`.

O componente não recebe imagens do produto, mantendo-se desacoplado do
model `Product`. A imagem é resolvida via `Storage::url($path)` quando
`hero_image_path` começa com `catalog/hero/...`.

## Views do catálogo

### `catalog/home.blade.php`

Estrutura nova (com hero ativado):

```
<x-site.catalog-hero :settings="$settings" />
<section header> (título + intro — só se hero desativado)
<section> Categorias (chips)
<section> Busca rápida
<section> Destaques (com section-heading)
<section> Últimos produtos (com section-heading)
```

Com hero desativado, mantém o padrão atual (header → categorias → busca →
destaques → últimos) com pequenos refinamentos de espaçamento.

### `catalog/_product-card.blade.php`

- Badges com `x-ui.badge` mantidos; adiciona badge "Sob consulta" quando
  `price_display_mode === on_request`.
- Preço permanece no rodapé via `mt-auto`.
- Botões "Ver detalhes" (secondary) e "Consulte" (primary) com tamanhos
  consistentes (`size="sm"`).
- Adiciona `loading="lazy"` e `decoding="async"` na imagem.

### `catalog/index.blade.php`

- Header da listagem em um único bloco (título à esquerda, contagem e
  ordenação à direita), com `flex flex-wrap items-end justify-between`.
- Sidebar de filtros com `<details>` aberta por padrão no desktop;
  opcionalmente fechada no mobile.
- Empty state com botão "Limpar filtros".

### `catalog/category.blade.php`

- Breadcrumb com chevron SVG inline (substituir `›` por SVG).
- Header da categoria: nome em `font-display`, descrição em `text-sm`,
  chip com contagem.
- Subcategorias em chips arredondados com hover suave.

### `catalog/show.blade.php`

- Em mobile, CTA "Consulte" fixo no rodapé (Alpine `x-show` quando
  largura < lg).
- Bloco de preço/info permanece sticky no desktop (`lg:sticky lg:top-24`).
- Acordeões de descrição/especificações/características abrem
  independentemente (não aninhados).
- Breadcrumb com SVG chevron.

## `SeoService::forCatalogHome`

Ajusta `og:title` e `meta['title']` para usar `hero_title` quando ele
estiver presente e o admin não tiver preenchido `seo_title`. Mantém a
lógica atual como prioridade (`seo_title` continua prevalecendo).

## Testes

- `ManageCatalogSettingsTest`:
  - Cenário "Super Admin salva o hero com sucesso".
  - Cenário "Editor continua sem acesso (gate `catalog.settings.manage`)" —
    já coberto, manter.
- `PublicCatalogTest`:
  - Cenário "Home renderiza hero quando `hero_enabled=true`".
  - Cenário "Home oculta hero quando `hero_enabled=false`".
  - Cenário "CTA do hero aparece quando label e url estão preenchidos".

## Validação final

- Pint (`./scripts/format.sh` ou `vendor/bin/pint`).
- PHPStan (`composer analyse` ou `vendor/bin/phpstan`).
- PHPUnit (`./scripts/test.sh` ou `vendor/bin/phpunit`).
- `openspec validate 2026-07-28-improve-catalog-layout-hero --strict`.