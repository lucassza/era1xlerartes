# Arquitetura

O projeto é uma aplicação Laravel monolítica modular. A camada HTTP e o painel Filament recebem requisições, serviços concentram regras de aplicação reutilizáveis, modelos representam persistência e os recursos Blade/Vite entregam a interface.

## Organização

- `app/Http`: controllers, requests e middleware.
- `app/Services`: casos de uso e integrações de aplicação.
- `app/Models`: entidades Eloquent e relações.
- `app/Filament`: recursos e páginas administrativas.
- `app/Observers`: observers Eloquent (ex.: `UserObserver` para banimento, `CatalogObserver` para invalidação de cache).
- `app/Mail`: mailableables enfileirados.
- `database`: migrations, factories e seeders.
- `resources`: Blade, JavaScript e CSS.
- `routes`: rotas web e de console.
- `docker/`: imagens, Nginx e inicialização do PostgreSQL.

## Separação Admin × Site

A tabela `users` é única, mas o campo `type` (`UserType::Admin` ou `UserType::SiteUser`) diferencia o papel do usuário. `User::canAccessPanel` exige `type === Admin` **e** permissão `admin.access`, garantindo que usuários do site não acessem `/admin` mesmo que estejam autenticados. Admins também podem comentar e enviar depoimentos quando autenticados no site.

O fluxo de comunidade depende dos seguintes serviços:

- `EngagementService` centraliza submissão, aprovação, rejeição e banimento (com snapshot de nome/e-mail/cidade).
- `ProfanityFilter` aplica a lista configurável de `config/profanity.php` (palavras em PT-BR).
- `PostRatingAggregator` calcula média, contagem e distribuição de notas por post a partir dos comentários aprovados.

## Módulo de Catálogo (catálogo público + blog)

O módulo foi introduzido pela change OpenSpec `add-product-catalog` (proposal/design/specs em `openspec/changes/add-product-catalog/`). Adiciona catálogo público de produtos integrado ao blog existente.

**Domínio** (UUID v4 em todas as tabelas):
- `catalog_categories` (hierárquica via `parent_id`, validação anti-ciclo no model).
- `brands`.
- `products` com slug único, status (`draft|published|unavailable|archived`), `price_display_mode` (`show|on_request|hidden`), `availability`, `is_featured`, `sort_order`, `catalog_category_id` e `brand_id`.
- `attribute_groups` e `attributes` (informativos; sem variações compráveis).
- `attribute_group_catalog_category` (pivot).
- `product_attribute_values` (pivot polimórfica com `value`).
- `product_tag` (pivot, reaproveita `tags`).
- `related_products` (pivot com `sort_order`).
- `product_inquiries` com snapshot de `product_name` (preservado mesmo após exclusão do produto), `ip`, `user_agent`, `utm_json`, `origin`, `status` (`new|viewed|in_service|waiting_customer|answered|completed|discarded|spam`), `assignee_id`.
- `product_inquiry_status_histories` (histórico de transições).
- `product_inquiry_notes` (notas internas).
- `catalog_settings` (singleton, `id` incremental, espelha o padrão de `blog_settings`).

**Busca full-text**:
- Função `pt_unaccent` (Postgres) reaproveitada da migração `2026_07_23_050000_enable_post_search`.
- Coluna `search_tsv` + trigger + índices GIN/trigram em `products`.

**Serviços principais**:
- `CatalogEditorialService` — normaliza `status`/`published_at` e sincroniza mídias/atributos ao salvar no Filament.
- `CatalogCacheService` — Redis com chaves estáticas (`catalog:categories:tree|flat`, `catalog:brands:active`, `catalog:settings:current`); bypass automático em ambiente testing para evitar `__PHP_Incomplete_Class` ao deserializar modelos Eloquent no cache.
- `CatalogObserver` — invalida o cache do Redis ao salvar/excluir `CatalogCategory`, `Brand` ou `CatalogSetting`.
- `ProductInquiryService` — `markAsViewed`, `changeStatus` (com histórico e marcos `first_answered_at`/`completed_at`), `assign`, `addNote`, `store` (transação com snapshot), `dashboardStats`, `buildListQuery`.
- `InquiryNotificationService` — despacha `ProductInquiryAdminNotification` (admin) e `ProductInquiryVisitorConfirmation` (visitante) via `Mail::to()->queue()`. Falhas de SMTP são capturadas por destinatário e registradas via `Log::warning`, sem derrubar a consulta.
- `SeoService` ganhou `forCatalogHome`, `forCatalogIndex`, `forCatalogCategory`, `forProduct` (com JSON-LD Product + BreadcrumbList) e `breadcrumbList()`.

**Camada pública** (Blade + Alpine):
- `CatalogController@index` — home do catálogo em `/` com banner, intro, categorias, busca, destaques, últimos produtos.
- `PublicProductController` — listagem filtrada em `/produtos` (categoria, marca, disponibilidade, faixa de preço, destaques) e detalhe em `/produtos/{slug}` (galeria com lightbox, atributos, anexos, relacionados, JSON-LD, OG/Twitter).
- `PublicCatalogCategoryController` — listagem hierárquica em `/categorias/{slug}`.
- `PublicProductInquiryController` — `POST /produtos/{slug}/consulta` com rate-limit por IP, honeypot, snapshot do produto.
- `site.contact.blade.php` — página `/contato` com telefone, WhatsApp e e-mails (lidos de `catalog_settings`).

**Painel Filament** (grupo de navegação `Catálogo`):
- `ProductResource` (form completo em 7 abas, mídia via Media Library, atributos, relacionados, SEO).
- `CatalogCategoryResource` (hierarquia, anti-ciclo).
- `BrandResource` (logo, website).
- `AttributeGroupResource` (com ação inline "Adicionar atributo").
- `ProductInquiryResource` (listagem com badge de não-lida, filtros, view com histórico, export CSV).
- `ManageCatalogSettings` (gate `catalog.settings.manage`).

**ACL** (13 permissões novas no `RolesAndPermissionsSeeder`):
`catalog.products.{view,create,update,delete,publish}`, `catalog.categories.manage`, `catalog.brands.manage`, `catalog.attributes.manage`, `catalog.inquiries.{view,update,assign,export}`, `catalog.settings.manage`. Policies: `ProductPolicy`, `CatalogCategoryPolicy`, `BrandPolicy`, `ProductInquiryPolicy`. Editor recebe todas exceto `catalog.settings.manage`; Author não tem acesso ao catálogo.

**SEO**:
- `/sitemap.xml` inclui produtos publicados e categorias ativas.
- `/rss.xml` mantém apenas artigos do blog (regra da change).
- `/robots.txt` permite crawling público e bloqueia `/admin`, `/perfil`, `/dashboard`, `/depoimentos`; aponta para o sitemap.
- JSON-LD: `WebSite` na home, `Product`/`Brand` no detalhe do produto, `BreadcrumbList` em categoria e produto.
- `BlogPosting` e `BreadcrumbList` no detalhe do post preservados.

**Compatibilidade**:
- URLs existentes do blog preservadas: `/posts/{slug}`, `/categories/{slug}`, `/tags/{slug}`, `/authors/{slug}`, `/rss.xml`, `/sitemap.xml`. O blog ganha rota dedicada em `/blog` (mesma view `welcome`).
- A home (`/`) deixa de ser a listagem de artigos e passa a ser o catálogo público. A listagem de artigos migrou para `/blog`.

Evite criar uma camada nova quando uma existente atende ao caso. Mudanças arquiteturais exigem autorização e documentação nesta página.