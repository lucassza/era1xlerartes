# Spec: Catálogo público

## ADDED Requirements

### Requirement: Página inicial exibe o catálogo

A rota `/` SHALL exibir o catálogo de produtos contendo banner/seção de
destaque configurável, título e texto introdutório (de `catalog_settings`),
produtos em destaque, categorias ativas, campo de pesquisa, filtros e listagem
paginada. A listagem de artigos do blog SHALL ser removida da home.

#### Scenario: Visitante abre a home

- **WHEN** um visitante acessa `/`
- **THEN** vê o catálogo com título, intro, destaques, categorias, busca,
  filtros e produtos publicados paginados, sem nenhuma listagem de posts

#### Scenario: Catálogo sem produtos publicados

- **WHEN** não existem produtos publicados
- **THEN** a home exibe o estado vazio amigável e as categorias, sem erro técnico

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
