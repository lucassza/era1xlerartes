# Proposta: Hero configurável e refinamento visual do catálogo

## Why

A home do catálogo (`/`) é a porta de entrada comercial do site, mas hoje:

1. O "banner" da home é renderizado a partir de um campo JSON livre
   (`catalog_settings.banner`) sem nenhum formulário no painel — o admin não
   tem como preenchê-lo sem mexer no banco.
2. O visual atual é pobre: blocos com `border` e tipografia pequena, sem
   hierarquia editorial. Não passa a sensação de "vitrine comercial" que o
   negócio precisa.
3. A listagem (`/produtos`), a categoria (`/categorias/{slug}`) e o detalhe
   (`/produtos/{slug}`) funcionam, mas têm pequenos atritos: filtros
   desconfortáveis no mobile, ausência de CTA sticky, header de listagem
   inconsistente, breadcrumb estilizado de forma diferente do restante.

Queremos uma home com hero configurável e um polish visual coerente nas
demais telas do catálogo, sem alterar regras de negócio nem rotas públicas.

## What Changes

- Substituir o JSON `banner` da tabela `catalog_settings` por colunas
  dedicadas para o hero (`hero_enabled`, `hero_title`, `hero_subtitle`,
  `hero_cta_label`, `hero_cta_url`, `hero_image_path`, `hero_alignment`).
- Adicionar seção **"Hero da home"** em `ManageCatalogSettings` (Filament)
  com toggle mestre, textos, CTA, upload de imagem e alinhamento. Permissão
  existente `catalog.settings.manage` continua sendo o gate.
- Novo componente Blade `<x-site.catalog-hero>` que renderiza o hero a
  partir das colunas; suporta variante com imagem de fundo (overlay
  gradiente) e variante apenas com gradiente.
- Refatorar `catalog/home.blade.php`: hero no topo, header do catálogo
  (título + intro) deslocado para baixo quando o hero está ativo,
  categorias em chips com hover consistente, busca rápida com ícone,
  seções de destaques e últimos produtos com cabeçalho padronizado via
  `<x-ui.section-heading>`.
- Polish visual em `catalog/_product-card.blade.php`,
  `catalog/index.blade.php`, `catalog/category.blade.php` e
  `catalog/show.blade.php` (alinhamentos, hierarquia tipográfica,
  tratamento de mobile, CTA sticky).
- `SeoService::forCatalogHome` usa `hero_title` como fallback do
  `og:title` quando presente.
- Migration nova (sem destructive change): adiciona colunas; a coluna
  `banner` (JSON) é preservada nesta mudança mas deixada sem leitura no
  frontend. Não removemos `banner` para evitar perda acidental de dados
  já preenchidos em ambientes existentes — uma migration futura poderá
  removê-la com segurança caso seja desejado.

## Non-goals

- Não adicionar galeria/carousel no hero.
- Não criar múltiplos hero slots, agendamento por data ou variantes A/B.
- Não tocar na home do blog (`/blog`); ela já tem hero editorial baseado
  no post mais recente.
- Não criar novas rotas públicas, novos controllers ou novas
  permissões.
- Não instalar dependências novas; usar apenas Filament Forms, Alpine e
  Blade já presentes.
- Não reescrever o `CatalogController` ou o serviço de cache — apenas
  ajustar a view e o `SeoService`.

## Impact

- **Migrations**: 1 nova (adição de colunas em `catalog_settings`).
- **Models**: `CatalogSetting` ganha campos em `fillable`/`casts`/`defaults`.
- **Painel**: `ManageCatalogSettings` ganha 1 seção (Hero).
- **Views públicas**: 5 arquivos editados + 1 componente novo.
- **Serviços**: `SeoService::forCatalogHome` com fallback de `og:title`.
- **Testes**: ajuste em `ManageCatalogSettingsTest` e `PublicCatalogTest`.
- **ACL**: nenhuma permissão nova.
- **Cache**: o `CatalogObserver` já invalida `catalog:settings:current`
  em qualquer `save`/`delete` de `CatalogSetting`, então a nova aba Hero
  não exige ajuste no `CatalogCacheService`.