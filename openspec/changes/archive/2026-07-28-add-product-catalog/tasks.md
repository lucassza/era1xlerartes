# Tarefas: Catálogo de produtos integrado ao blog

## 1. Fundação de dados

- [ ] 1.1 Criar enums `ProductStatus`, `PriceDisplayMode`, `ProductAvailability`, `InquiryStatus` (string-backed, `label()`/`color()` em PT-BR)
- [ ] 1.2 Migration e model `catalog_categories` (UUID, `parent_id` auto-ref, slug, ordem, SEO, validação anti-ciclo) + factory
- [ ] 1.3 Migration e model `brands` (UUID, slug, logo, ordem) + factory
- [ ] 1.4 Migration e model `products` (UUID, FKs, preços, `price_display_mode`, `availability`, destaque, ordem, SEO, status) + factory
- [ ] 1.5 Migration de busca full-text em `products` (`search_tsv` + trigger + GIN com `pt_unaccent`, padrão de posts)
- [ ] 1.6 Migrations `attribute_groups`, `attributes`, `attribute_group_catalog_category`, `product_attribute_values` + models/factories
- [ ] 1.7 Migration pivot `product_tag` reutilizando `tags` existente
- [ ] 1.8 Migration `related_products` (par único, ordenação) + relacionamento no model
- [ ] 1.9 Migrations `product_inquiries`, `product_inquiry_status_histories`, `product_inquiry_notes` + models/factories
- [ ] 1.10 Migration e model singleton `catalog_settings` (id incremental, padrão `BlogSetting`) com `defaults()`
- [ ] 1.11 Estender `BlogMediaPathGenerator` com prefixos `catalog/{products|categories|brands}` e registrar coleções de mídia nos models
- [ ] 1.12 Rodar migrations, Pint e PHPStan

## 2. ACL e seeders

- [ ] 2.1 Adicionar as 13 permissões `catalog.*` ao `RolesAndPermissionsSeeder` (Super Admin: todas; Editor: sem `settings.manage`)
- [ ] 2.2 Criar `ProductPolicy`, `CatalogCategoryPolicy`, `BrandPolicy`, `ProductInquiryPolicy` e registrar em `AuthServiceProvider`
- [ ] 2.3 Criar `CatalogDemoSeeder` (categorias, marcas, atributos, produtos em todos os status, consultas em vários status) sem serviços externos
- [ ] 2.4 Testes de permissões e do seeder

## 3. Painel — Catálogo (Filament)

- [ ] 3.1 `CatalogCategoryResource` (hierarquia, ordem, SEO, mídia) no grupo "Catálogo"
- [ ] 3.2 `BrandResource` (logo, site, ordem)
- [ ] 3.3 `AttributeGroupResource` + gestão de atributos e associação a categorias
- [ ] 3.4 `ProductResource`: formulário completo (abas), galeria ordenável, anexos, tags, atributos, relacionados, SEO, status, destaque, ordem
- [ ] 3.5 Página `ManageCatalogSettings` com gate `catalog.settings.manage`
- [ ] 3.6 Registrar grupo de navegação "Catálogo" no `AdminPanelProvider`
- [ ] 3.7 Testes Feature das resources e das settings

## 4. Painel — Consultas e dashboard

- [ ] 4.1 `ProductInquiryResource`: listagem com indicador de não lida, filtros (período, produto, categoria, status, responsável, origem), ordenação
- [ ] 4.2 Visualização da consulta (infolist) com histórico de status/responsáveis, observações internas e `first_viewed_at` na abertura
- [ ] 4.3 Actions: alterar status (com marcos `first_answered_at`/`completed_at`), atribuir responsável, notas, spam, concluir, copiar contato, WhatsApp
- [ ] 4.4 Exportação CSV streamed respeitando filtros, protegida por `catalog.inquiries.export`
- [ ] 4.5 Widgets no `Dashboard`: novas, em atendimento, concluídas, por período, top produtos/categorias, origem, taxa de conclusão, tempo médio de 1º atendimento
- [ ] 4.6 Testes Feature de listagem, ações, permissões e exportação

## 5. Público — Catálogo

- [ ] 5.1 Scopes no `Product` (`published`, `visible`, `featured`, `search`, filtros, ordenações) e `ProductSearchService`
- [ ] 5.2 `CatalogController@index` (home) com destaques, categorias e paginação configurável + cache de settings
- [ ] 5.3 `PublicProductController@index/show` (`/produtos`, `/produtos/{slug}`) com eager loading e estados visuais
- [ ] 5.4 `PublicCatalogCategoryController@show` (`/categorias/{slug}`) incluindo produtos de subcategorias
- [ ] 5.5 Views Blade: home do catálogo, listagem (card, filtros GET, ordenação, paginação com query string), detalhe (galeria Alpine com zoom, anexos, relacionados, breadcrumbs), página de categoria
- [ ] 5.6 Cache de categorias/marcas/settings com invalidação por observers
- [ ] 5.7 Testes Feature: home, listagem, rascunho bloqueado, busca, filtros, ordenação, detalhe, categoria, 404s

## 6. Público — Consulta

- [ ] 6.1 `StoreProductInquiryRequest` (validação, honeypot `website`, limites, consentimento LGPD)
- [ ] 6.2 `ProductInquiryService::store()` em transaction (snapshot do nome, histórico inicial, sanitização, rate limit 5/10min por IP)
- [ ] 6.3 Rota `POST /produtos/{product}/consulta` + controller enxuto + modal Alpine nas views
- [ ] 6.4 Evento `ProductInquiryReceived` + listeners com mailables enfileirados (admins + confirmação opcional) e log de falhas
- [ ] 6.5 Testes Feature: envio, validação, honeypot, rate limit, snapshot, fila de notificações, falha de e-mail não perde consulta

## 7. Blog e navegação

- [ ] 7.1 `BlogController@index` em `/blog` (listagem paginada de artigos) + view
- [ ] 7.2 Estender `PublicNavigationService` e atualizar header/footer/menu mobile (Início, Produtos, Categorias, Blog, Contato)
- [ ] 7.3 Ajustar `HomeController` antigo → substituído pelo catálogo; garantir rotas do blog intactas
- [ ] 7.4 Testes: `/blog` lista artigos, `/posts/{slug}` e `/rss.xml` respondem 200 como antes

## 8. SEO

- [ ] 8.1 `SeoService`: sitemap com produtos e categorias de catálogo (RSS continua só com posts)
- [ ] 8.2 Meta/OG/Twitter/JSON-LD de produto e da home do catálogo; breadcrumbs
- [ ] 8.3 Testes de sitemap e metadados

## 9. Qualidade e documentação

- [ ] 9.1 Suíte completa (`composer test`), Pint (`composer lint`), PHPStan (`composer analyse`)
- [ ] 9.2 Atualizar `docs/architecture.md`, `docs/database.md`, `docs/api.md` com o módulo de catálogo
- [ ] 9.3 Atualizar `docs/agents.md` com o fluxo OpenSpec
- [ ] 9.4 Revisão final contra os critérios de aceite e `openspec validate`
