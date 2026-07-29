# catalog-public Specification

## Purpose
TBD - created by archiving change add-product-catalog. Update Purpose after archive.
## Requirements
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

### Requirement: Listagem de produtos em `/produtos`

A rota `/produtos` SHALL listar apenas produtos com status `published`
(e `unavailable` quando a setting `show_unavailable` estiver ativa), com
paginação configurável e eager loading de capa, categoria e marca.

#### Scenario: Produto em rascunho não aparece

- **WHEN** existe um produto com status `draft`
- **THEN** ele não aparece em `/produtos`, na home nem em buscas públicas

#### Scenario: Produto indisponível oculto por configuração

- **WHEN** `show_unavailable` está desativada e um produto está `unavailable`
- **THEN** ele não aparece nas listagens públicas

### Requirement: Card de produto

Cada card SHALL exibir foto principal, nome, descrição curta, categoria, selo
(destaque, lançamento ou indisponível, quando aplicável), botão "Ver detalhes"
e botão "Consulte". O preço SHALL seguir o modo configurado no produto:
exibir valor, exibir "Sob consulta" ou ocultar.

#### Scenario: Preço em modo "Sob consulta"

- **WHEN** o produto está com `price_display_mode = on_request`
- **THEN** o card e o detalhe exibem o texto "Sob consulta" e nenhum valor

#### Scenario: Preço oculto

- **WHEN** o produto está com `price_display_mode = hidden`
- **THEN** nenhuma informação de preço é exibida

### Requirement: Pesquisa de produtos

O catálogo SHALL pesquisar por nome, código interno, SKU, descrição, marca e
tags usando full-text Postgres (pt_unaccent), retornando apenas produtos
visíveis publicamente.

#### Scenario: Busca por SKU

- **WHEN** o visitante pesquisa um SKU existente de produto publicado
- **THEN** o produto correspondente aparece nos resultados

#### Scenario: Busca sem resultados

- **WHEN** a pesquisa não encontra produtos
- **THEN** exibe estado de "nenhum resultado" com opção de limpar filtros

### Requirement: Filtros e ordenação preservados na URL

O catálogo SHALL filtrar por categoria, subcategoria, marca, destaque,
disponibilidade, faixa de preço (somente quando preços são públicos) e
atributos configuráveis, e SHALL ordenar por mais recentes, nome A–Z, nome
Z–A, menor preço, maior preço, destaque e ordem manual. Filtros e ordenação
SHALL ser preservados na query string em todos os links de paginação.

#### Scenario: Filtro por categoria com paginação

- **WHEN** o visitante filtra por uma categoria e navega para a página 2
- **THEN** a URL mantém `?categoria=...&page=2` e os resultados continuam filtrados

#### Scenario: Faixa de preço com preços privados

- **WHEN** a exibição de preços está desativada nas settings
- **THEN** o filtro de faixa de preço não é exibido nem aplicado

### Requirement: Página de detalhes `/produtos/{slug}`

A página SHALL exibir nome, código/SKU, foto principal, galeria com zoom,
descrições curta e completa, categoria, subcategoria, marca, características,
especificações, atributos, preço conforme modo, disponibilidade, arquivos
anexos, produtos relacionados e botão "Consulte" (visível também no mobile),
com metadados SEO, breadcrumbs e JSON-LD de produto.

#### Scenario: Detalhe de produto publicado

- **WHEN** o visitante acessa `/produtos/{slug}` de um produto publicado
- **THEN** vê todos os blocos acima, galeria navegável e JSON-LD válido

#### Scenario: Detalhe de produto não publicado

- **WHEN** o slug pertence a produto `draft` ou `archived`
- **THEN** responde 404 público (sem dados do produto)

### Requirement: Página de categoria `/categorias/{slug}`

A rota SHALL listar produtos publicados da categoria e suas subcategorias, com
breadcrumbs, descrição e SEO da categoria, sem conflitar com
`/categories/{slug}` do blog.

#### Scenario: Categoria com subcategorias

- **WHEN** a categoria possui subcategorias com produtos
- **THEN** a listagem inclui produtos das subcategorias

#### Scenario: Categoria inexistente

- **WHEN** o slug não existe ou a categoria está inativa
- **THEN** responde 404

### Requirement: Responsividade e estados visuais

Todas as páginas do catálogo SHALL ser responsivas (desktop, tablet, celular),
acessíveis e SHALL ter estados para carregamento, lista vazia, erro, sucesso,
produto indisponível e pesquisa sem resultados, sem mensagens técnicas.

#### Scenario: Acesso mobile ao detalhe

- **WHEN** o visitante abre um produto em viewport móvel
- **THEN** o botão "Consulte" permanece visível e a galeria é utilizável por toque

### Requirement: Cache de dados públicos

Árvore de categorias ativas, marcas ativas e configurações do catálogo SHALL
ser cacheadas (Redis) e invalidadas automaticamente quando alteradas no painel.

#### Scenario: Categoria criada no painel

- **WHEN** um administrador cria ou altera uma categoria de catálogo
- **THEN** o cache público é invalidado e a próxima visita já reflete a mudança

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

