# Design: Catálogo de produtos integrado ao blog

## Context

Monolito Laravel 13 + Filament 5 que opera como blog (TDB Mix). Base já
consistente e reaproveitável: UUIDs v4 em todas as tabelas de domínio, enums
string-backed, ACL artesanal com Spatie Permission (sem Shield), Media Library
com MinIO/S3, busca full-text Postgres pt_unaccent em posts, honeypot + rate
limit manuais, singleton `BlogSetting` para configurações, frontend público em
Blade + Alpine (Livewire 4 apenas no painel), PHPUnit 12 com guarda de banco de
testes.

## Goals / Non-Goals

**Goals:**

- Catálogo público em `/` com busca, filtros, ordenação e detalhe de produto,
  sem nenhum fluxo de compra.
- Consulta comercial registrada no banco antes de qualquer notificação, com
  gestão completa no painel.
- Reuso máximo dos padrões existentes (models, services, policies, mídia,
  settings, SEO, testes).
- Zero quebra de URLs do blog e do RSS.

**Non-Goals:** ver "Não-objetivos" em `proposal.md`.

## Decisions

### D1 — URLs do blog preservadas; `/blog` vira listagem de artigos

As rotas `/posts/{slug}`, `/categories/{slug}`, `/tags/{slug}`,
`/authors/{slug}` e `/rss.xml` permanecem idênticas (URLs indexadas não
quebram, sem necessidade de redirects 301). Cria-se `BlogController@index` em
`/blog` assumindo o papel de listagem que hoje a home cumpre. A home passa a
ser `CatalogController@index`.
**Alternativa considerada:** migrar artigos para `/blog/{slug}` com redirects —
rejeitada por adicionar risco de SEO sem ganho real.

### D2 — Imagens e anexos via Media Library (sem `product_images`/`product_files`)

O model `Product` usa coleções `cover` (single), `gallery` (ordenável) e
`attachments` (PDF/manuais); `CatalogCategory` usa `image`; `Brand` usa `logo`.
Conversões `webp` + `thumb-300/600/1200` replicando o padrão de `Post`.
`BlogMediaPathGenerator` é estendido com prefixos `catalog/{products|categories|brands}/{id}/`.
**Alternativa considerada:** tabelas próprias de imagens/arquivos — rejeitada
por duplicar o que a Media Library já entrega (ordenação, conversões, URLs
temporárias privadas, remoção em cascata).

### D3 — Singleton `CatalogSetting` espelhando `BlogSetting`

Nova tabela `catalog_settings` com `id` incremental (única exceção ao padrão
UUID, igual a `blog_settings`) e página Filament `ManageCatalogSettings` com
gate `catalog.settings.manage`. Centraliza: título, texto introdutório,
banner, itens por página, ordenação padrão, exibição de preços, exibição de
indisponíveis, e-mails de notificação, mensagem de confirmação, texto do botão
"Consulte", telefone/WhatsApp comercial, flag de CAPTCHA, SEO da home.
**Alternativa considerada:** spatie/laravel-settings — rejeitada por adicionar
dependência e divergir do padrão já estabelecido.

### D4 — Consulta salva em transação antes de notificar; notificações por fila

`ProductInquiryService::store()` executa em transaction: cria
`product_inquiries` (com snapshot `product_name`, `product_id` nullable com
`nullOnDelete`), registra o histórico inicial em
`product_inquiry_status_histories` e só então despacha
`ProductInquiryReceived` (event) → listeners enfileiram mailables (admins
configurados + confirmação opcional ao visitante). Falha de e-mail nunca perde
a consulta; falhas são logadas sem dados pessoais desnecessários.
Anti-spam: honeypot `website` (prohibited), `RateLimiter` 5/10min por IP,
sanitização dos campos, limite de mensagem, registro de tentativas suspeitas.
Padrão idêntico ao `EngagementService` + observers de comentários/depoimentos.

### D5 — Busca full-text de produtos replicando o padrão de posts

Migration habilita coluna `search_tsv` em `products` com trigger e índice GIN
usando a config `pt_unaccent` já existente (criada na migration
`2026_07_23_050000_enable_post_search`). `ProductSearchService` espelha
`PostSearchService`. Filtros (categoria, marca, disponibilidade, faixa de
preço, atributos) são scopes Eloquent; ordenação inclui `sort_order` manual e
destaque. Filtros preservados via query string (`request->query()` nos links e
no formulário GET).
**Alternativa considerada:** Laravel Scout + driver externo — rejeitada por
adicionar infra desnecessária; o full-text nativo já atende o blog.

### D6 — ACL artesanal com 13 permissões `catalog.*`

Permissões: `catalog.products.{view,create,update,delete,publish}`,
`catalog.categories.manage`, `catalog.brands.manage`,
`catalog.attributes.manage`, `catalog.inquiries.{view,update,assign,export}`,
`catalog.settings.manage`. Seed em `RolesAndPermissionsSeeder` (Super Admin:
todas; Editor: gestão de produtos e consultas, sem `settings.manage`; Author:
sem acesso ao catálogo). Policies novas registradas explicitamente no mapa de
`AuthServiceProvider`; resources Filament consultam as policies; menus/botões
usam `can()` — nunca apenas ocultação.

### D7 — Frontend público em Blade + Alpine, formulário com POST tradicional

Listagem e detalhe em Blade com os componentes UI existentes; modal "Consulte"
e galeria com zoom em Alpine; formulário submete POST tradicional com
`StoreProductInquiryRequest` (redirect back com feedback de sucesso/erro).
Sem componentes Livewire novos no público.
**Alternativa considerada:** Livewire no front — rejeitada para não divergir do
padrão atual (Livewire só no Filament).

### D8 — Categorias de catálogo separadas das categorias do blog

`catalog_categories` é hierárquica (auto-referência `parent_id`) e distinta da
tabela `categories` dos posts — domínios diferentes, rotas diferentes
(`/categorias/*` vs `/categories/*`). Validação impede ciclos (ancestralidade
verificada no save e na Form Request). Tags do blog são **reutilizadas** para
produtos via pivot `product_tag` (evita tabela redundante).

### D9 — Consultas de leitura otimizadas e cache público

Eager loading (`cover`, `category`, `brand`) em todos os cards; scopes de
filtro combináveis; paginação com query string. Cache (Redis) para árvore de
categorias ativas, marcas ativas e `CatalogSetting`, invalidado por observers
nos models correspondentes. Widgets do dashboard usam agregações (`count`,
`groupBy`) — nunca coleções em memória.

### D10 — Exportação CSV nativa

`ProductInquiryExportController` gera CSV streamed (BOM UTF-8) respeitando os
filtros da listagem, protegido por `catalog.inquiries.export`.
**Alternativa considerada:** `maatwebsite/excel` — rejeitada por ser
dependência pesada para o requisito (CSV já atende).

## Risks / Trade-offs

- [Conflito de slug entre `/categorias/{slug}` e rotas do blog] → prefixos
  distintos (`categorias` vs `categories`) e testes de rota cobrindo ambos.
- [Volume de alterações em uma única entrega] → implementação faseada em
  `tasks.md` com testes por fase; nenhuma fase quebra o blog.
- [E-mails de notificação falharem] → consulta já persistida; falha logada;
  processamento em fila Redis com retry.
- [Spam no formulário público] → honeypot + rate limit + sanitização + status
  `spam`; CAPTCHA fica como flag preparada para integração futura.
- [Produto excluído após consultas] → `product_id` nullable + snapshot
  `product_name`; consulta permanece íntegra no painel.
- [Hierarquia de categorias com ciclo] → validação de ancestralidade no model
  e na Form Request + teste dedicado.
- [URLs privadas do MinIO expostas] → URLs temporárias assinadas via Media
  Library; nenhuma credencial em resposta pública.

## Migration Plan

1. Aplicar migrations novas (aditivas, sem alterar tabelas existentes).
2. Rodar `RolesAndPermissionsSeeder` (idempotente) para criar permissões.
3. Rodar `CatalogDemoSeeder` apenas em ambiente de demonstração.
4. Deploy sem janela de manutenção: o blog continua no ar; a troca da home é
   apenas mudança de controller na rota `/`.
5. Rollback: reverter deploy e `migrate:rollback` do batch do catálogo (sem
   impacto nas tabelas do blog).

## Open Questions

- Item "Contato" do menu: página dedicada, âncora do rodapé ou formulário de
  consulta geral? (padrão proposto: âncora do rodapé até haver página de contato)
- Textos finais de LGPD e link da política de privacidade (existe página de
  política hoje? Se não, criar placeholder configurável nas settings).
