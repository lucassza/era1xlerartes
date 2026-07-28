# Banco de dados

O ambiente utiliza PostgreSQL como banco principal, Redis para cache, sessão e filas, e MinIO como armazenamento S3 compatível. O schema é versionado em `database/migrations`.

## Operações comuns

```bash
docker compose exec app php artisan migrate
docker compose exec app php artisan db:seed
docker compose exec app php artisan migrate:fresh --seed
```

Nunca execute `migrate:fresh` em ambientes compartilhados ou de produção sem confirmação explícita. Credenciais e URLs devem permanecer em `.env`, nunca em código ou documentação com valores reais.

## Engajamento da comunidade

A camada de comunidade foi desenhada para que **toda submissão pública passe por aprovação do administrador** antes de aparecer no blog. As tabelas envolvidas:

- `users.type` (`admin` | `site`) — diferencia usuários do painel Filament de usuários autenticados do site público.
- `users.city`, `users.banned_at` — dados complementares e bloqueio administrativo.
- `blog_settings.comments_enabled`, `blog_settings.testimonials_enabled` — toggles globais que controlam disponibilidade dos recursos.
- `posts.comments_locked` — override por post para bloquear comentários mesmo com a flag global ativa.
- `testimonials` — depoimentos enviados via `/depoimentos` com snapshot de `name`, `email` e `city` (caso o usuário seja removido).
- `post_comments` — comentários enviados em `posts/{slug}` com snapshot, score e status.

Toda submissão pública é criada com `status = pending` e exige ação do administrador (Filament) para virar `approved` ou `rejected`. O `EngagementService` aplica filtro de palavrões (`config/profanity.php`), honeypot e rate limit por usuário antes de gravar; rejeições automáticas ficam com `rejection_reason = profanity_auto`.

## Catálogo de produtos

O catálogo público foi adicionado pelas migrations `2026_07_27_100000..100800`. Todas as tabelas seguem o padrão UUID v4 do projeto; `catalog_settings` é a única com `id` incremental (singleton). Soft deletes não são usados.

- `catalog_categories` — categorias hierárquicas (auto-relação via `parent_id`). Validação anti-ciclo implementada em `CatalogCategory::guardAgainstCycle` chamada no hook `saving`.
- `brands` — marcas com `logo` (Media Library) e `website`.
- `products` — slug único, `internal_code`, `sku`, `short_description`, `description`, `specifications`, `features`, `price` e `promotional_price` (apenas informativos), `price_display_mode` (`show|on_request|hidden`), `availability`, `is_featured`, `sort_order`, `status` (`draft|published|unavailable|archived`), `published_at`, `seo_title`, `seo_description`. FKs para `catalog_categories` (`nullOnDelete`) e `brands` (`nullOnDelete`). Índices em `(status, published_at)`, `(status, is_featured)`, `sku`, `sort_order`.
- `attribute_groups`, `attributes`, `attribute_group_catalog_category` (pivot), `product_attribute_values` (pivot com `value`) — atributos informativos para produtos.
- `product_tag` — pivot reaproveitando `tags` (do blog) com cascata.
- `related_products` — pivot com `sort_order` e chave composta.
- `product_inquiries` — consultas públicas com snapshot de `product_name` (preservado se o produto for excluído), `ip`, `user_agent`, `utm_json` (`source/medium/campaign/term/content`), `origin` (`web|whatsapp|...`), `status` (`new|viewed|in_service|waiting_customer|answered|completed|discarded|spam`), `assignee_id` (FK users com `nullOnDelete`), marcos temporais (`first_viewed_at`, `first_answered_at`, `completed_at`).
- `product_inquiry_status_histories` — `from_status`, `to_status`, `user_id`, `note`, `created_at` (sem `updated_at`, somente timestamp de criação).
- `product_inquiry_notes` — observações internas vinculadas ao usuário autor.
- `catalog_settings` — singleton (`id` incremental) com chaves: `title`, `intro_text`, `banner` (json), `per_page`, `default_sort`, `show_unavailable`, `inquiry_button_text`, `inquiry_recipients`, `inquiry_confirmation_message`, `commercial_phone`, `commercial_whatsapp`, `captcha_enabled`, `seo_title`, `seo_description`.

### Busca full-text

A migração `2026_07_27_100800_enable_product_search` reaproveita a função `pt_unaccent` e adiciona:

- Coluna `search_tsv` (tsvector) em `products` populada por trigger `products_search_tsv_trigger` com pesos A (nome, SKU, código interno) e B/C (descrições).
- Índice GIN em `search_tsv` e em colunas `name`, `sku` para busca trigram (`pg_trgm`).
- Consulta no `Product::scopeSearchTerm` usa `search_tsv @@ plainto_tsquery('public.pt_unaccent', ?)`.

### Mídia

Mídia de produto e marca é gerenciada via `spatie/laravel-medialibrary` (já configurado no blog). Coleções registradas no `Product`:

- `cover` — single, imagem principal.
- `gallery` — múltipla, ordenável, imagens adicionais.
- `attachments` — múltipla, PDF/manuais/fichas técnicas (extensões `.pdf`, `.doc`, `.docx`, `.xls`, `.xlsx` permitidas; `BlogMediaPathGenerator` foi estendido com `catalog/{products|categories|brands}/{id}/`).

### Cache

`CatalogCacheService` armazena em Redis as chaves `catalog:categories:tree`, `catalog:categories:flat`, `catalog:brands:active`, `catalog:settings:current` (TTL de 600s). A invalidação é feita pelo `CatalogObserver` em `saved`/`deleted` das três entidades. Em ambiente `testing` o cache é totalmente bypassado para evitar `__PHP_Incomplete_Class` durante a deserialização de modelos Eloquent.