# Spec: Hero configurável na home pública

## ADDED Requirements

### Requirement: Home exibe hero configurado pelo admin

A página inicial do catálogo (`/`) SHALL renderizar um hero no topo
sempre que `catalog_settings.hero_enabled` for verdadeiro. Quando o hero
está desativado, a home SHALL manter o cabeçalho padrão com título e
texto introdutório, sem bloco de hero.

#### Scenario: Hero desativado preserva o cabeçalho atual

- **WHEN** o admin deixa `hero_enabled` desligado (padrão)
- **THEN** a home exibe o título e o texto introdutório de
  `catalog_settings` no padrão atual, sem hero

#### Scenario: Hero ativado renderiza imagem, título, subtítulo e CTA

- **WHEN** `hero_enabled=true`, `hero_title="Linha completa"`,
  `hero_subtitle="Atendimento comercial em todo Brasil"`,
  `hero_cta_label="Ver produtos"`, `hero_cta_url="/produtos"` e
  `hero_image_path="catalog/hero/banner.jpg"`
- **THEN** a home exibe um `<section>` com imagem de fundo, overlay,
  título, subtítulo e botão CTA apontando para `/produtos`

#### Scenario: CTA oculto quando label ou url estiverem vazios

- **WHEN** `hero_enabled=true` mas `hero_cta_label` ou `hero_cta_url`
  estiverem vazios
- **THEN** o hero é renderizado sem botão CTA

#### Scenario: Alinhamento centralizado

- **WHEN** `hero_alignment="center"`
- **THEN** o conteúdo do hero (título, subtítulo, CTA) é centralizado
  horizontalmente

### Requirement: Hero sem imagem usa gradiente

A página inicial SHALL renderizar o hero com fundo gradiente
(primária → secundária) quando `hero_image_path` for vazio,
independente do estado do toggle.

#### Scenario: Hero sem imagem e com imagem

- **WHEN** `hero_enabled=true` e `hero_image_path` é vazio
- **THEN** o hero exibe fundo gradiente e nenhum `<img>` é emitido
- **AND WHEN** `hero_image_path="catalog/hero/banner.jpg"`
- **THEN** o hero exibe a imagem via `Storage::url(...)`

### Requirement: SEO usa título do hero como fallback

`SeoService::forCatalogHome` SHALL usar `hero_title` como fallback do
`og:title` (e do `meta.title` quando `seo_title` estiver vazio).

#### Scenario: seo_title preenchido tem prioridade

- **WHEN** `seo_title="Página oficial"` e `hero_title="Catálogo X"`
- **THEN** o `<meta property="og:title">` retornado é "Página oficial"

#### Scenario: hero_title preenche og_title quando seo_title vazio

- **WHEN** `seo_title` é vazio e `hero_title="Catálogo X"`
- **THEN** o `<meta property="og:title">` retornado é "Catálogo X"

## MODIFIED Requirements

### Requirement: Página inicial exibe o catálogo

A rota `/` SHALL exibir o catálogo de produtos contendo hero configurável
quando `hero_enabled=true` (campos `hero_title`, `hero_subtitle`,
`hero_cta_label`, `hero_cta_url`, `hero_image_path`, `hero_alignment`),
ou cabeçalho tradicional (título + intro) quando desativado,
seguido por categorias ativas, campo de pesquisa, produtos em destaque
e últimos produtos. A listagem de artigos do blog SHALL continuar fora
da home.

#### Scenario: Visitante abre a home

- **WHEN** um visitante acessa `/`
- **THEN** vê o catálogo com hero (quando habilitado) ou título e intro,
  categorias, busca, destaques e últimos produtos paginados, sem nenhuma
  listagem de posts

#### Scenario: Catálogo sem produtos publicados

- **WHEN** não existem produtos publicados
- **THEN** a home exibe o estado vazio amigável e as categorias, sem erro técnico

#### Scenario: Estrutura básica da home permanece

- **WHEN** o visitante acessa `/`
- **THEN** vê (na ordem) hero OU cabeçalho, categorias, busca, destaques
  e últimos produtos, sem nenhuma listagem de posts