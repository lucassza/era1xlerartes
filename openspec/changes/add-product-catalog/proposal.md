# Proposta: Catálogo de produtos integrado ao blog

## Why

O site opera hoje apenas como blog. O negócio precisa expor produtos na página
inicial e capturar consultas comerciais dos visitantes, sem venda online.
Hoje não existe nenhuma estrutura de produtos, categorias de catálogo, marcas
ou registro de interessados — toda demanda comercial chega por canais informais
e não fica registrada no sistema.

## What Changes

- A rota `/` passa a exibir o **catálogo público de produtos** (destaques,
  busca, filtros, paginação); o blog ganha listagem própria em `/blog`.
- Novas rotas públicas: `/produtos`, `/produtos/{slug}`, `/categorias/{slug}`,
  `POST /produtos/{product}/consulta`.
- Menu público passa a ter: Início, Produtos, Categorias, Blog, Contato.
  **As URLs atuais dos artigos (`/posts/{slug}`, `/categories/{slug}`,
  `/tags/{slug}`, `/authors/{slug}`, `/rss.xml`) são preservadas sem redirect.**
- Módulo administrativo "Catálogo" no Filament: produtos (com galeria de
  imagens e anexos via Media Library/MinIO), categorias com subcategorias,
  marcas, atributos informativos e página de configurações do catálogo.
- Módulo administrativo "Consultas de produtos": listagem com indicador de não
  lida, visualização completa, mudança de status, atribuição de responsável,
  observações internas, exportação CSV e indicadores no dashboard.
- Consulta pública via botão "Consulte" (modal Alpine + POST): salva no banco
  **antes** de qualquer notificação; notificações por fila; honeypot,
  rate limit, sanitização e consentimento LGPD.
- 13 novas permissões `catalog.*` integradas ao ACL artesanal existente
  (seeder + policies + gates nas resources), sem instalar Filament Shield.
- Novas tabelas com UUID v4 (padrão do projeto): `catalog_categories`,
  `brands`, `products`, `product_tag`, `attribute_groups`, `attributes`,
  `attribute_group_catalog_category`, `product_attribute_values`,
  `related_products`, `product_inquiries`,
  `product_inquiry_status_histories`, `product_inquiry_notes` e
  `catalog_settings` (singleton incremental, mesmo padrão de `blog_settings`).
- Busca full-text de produtos replicando o padrão `search_tsv` + GIN
  (pt_unaccent) já usado em posts.
- `SeoService` estendido: produtos e categorias no `sitemap.xml`, JSON-LD de
  produto, canonical, OG/Twitter; RSS continua somente com artigos.
- Seeders de demonstração e suíte de testes cobrindo os fluxos principais.

## Capabilities

### New Capabilities

- `catalog-public`: catálogo público — home como vitrine, listagem paginada,
  pesquisa (nome, código, SKU, descrição, marca, tags), filtros (categoria,
  subcategoria, marca, destaque, disponibilidade, faixa de preço pública,
  atributos), ordenação, página de detalhes com galeria/zoom, anexos,
  produtos relacionados e SEO.
- `product-inquiry`: consulta de produto pelo visitante — botão "Consulte" em
  card e detalhe, formulário (modal ou página), validação, honeypot,
  rate limit, snapshot do nome do produto, registro com origem/UTM/IP,
  status inicial "nova", notificações enfileiradas e confirmação opcional.
- `catalog-admin`: gestão de catálogo no painel — CRUD de produtos (imagens,
  anexos, atributos, relacionados, SEO, status, destaque, ordem manual),
  categorias hierárquicas sem ciclos, marcas, grupos de atributos e
  configurações do catálogo.
- `inquiry-admin`: gestão das consultas no painel — listagem com filtros e
  indicador de não lida, visualização completa com histórico, mudança de
  status, atribuição de responsável, observações internas, ações rápidas
  (WhatsApp, copiar contato), exportação CSV e widgets de dashboard.
- `catalog-acl`: permissões granulares `catalog.*` aplicadas em rotas,
  policies, resources, menus e botões, integradas ao sistema de roles
  existente (Super Admin, Editor, Author).
- `blog-navigation`: reorganização da navegação pública — `/` vira catálogo,
  `/blog` lista artigos, menu com novas entradas, preservação das URLs
  existentes do blog e do feed RSS.

### Modified Capabilities

(nenhuma — não existem specs em `openspec/specs/`)

## Impact

- **Rotas**: `routes/web.php` — `/` muda de controller; novas rotas de
  catálogo e consulta; `/blog` novo. Sem conflito: `/categorias/*` ≠
  `/categories/*`. Nenhuma rota do blog removida.
- **Migrations**: 13 novas tabelas + extensão de busca full-text para
  `products`. Nenhuma tabela existente alterada.
- **Permissões**: `RolesAndPermissionsSeeder` ganha as 13 permissões
  `catalog.*`; `AuthServiceProvider` registra 4 novas policies
  (`ProductPolicy`, `CatalogCategoryPolicy`, `BrandPolicy`,
  `ProductInquiryPolicy`).
- **Código afetado**: `PublicNavigationService` (menu), `SeoService`
  (sitemap/home), `BlogMediaPathGenerator` (prefixos de catálogo),
  `AdminPanelProvider` (grupo de navegação "Catálogo"), `Dashboard` (widgets),
  views `components/site/header.blade.php` e `footer.blade.php`.
- **Dependências**: nenhuma nova. Reaproveita Media Library (imagens/anexos),
  fila Redis (notificações), honeypot/rate limit manuais e Media singleton
  de settings. CAPTCHA fica apenas como flag configurável, sem integração.
- **Exportação**: CSV nativo (sem `maatwebsite/excel`).
- **Ambiente**: nenhuma variável obrigatória nova; e-mails de notificação
  configuráveis na tela de settings do catálogo.

## Não-objetivos (Non-goals)

- Carrinho de compras, checkout, pagamento ou botão "Comprar".
- Variações compráveis de produto (atributos são apenas informativos).
- Integração real de CAPTCHA (apenas flag preparada).
- Exportação XLSX e resposta de consulta por e-mail integrada ao painel.
- Multi-tenancy, soft deletes e API pública para o catálogo.
- Migração das URLs de artigos para `/blog/{slug}`.
