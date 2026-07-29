# Tarefas: Hero configurável e refinamento visual do catálogo

## 1. Fundação de dados

- [ ] 1.1 Migration `2026_07_28_120000_add_hero_to_catalog_settings` adicionando colunas `hero_enabled`, `hero_title`, `hero_subtitle`, `hero_cta_label`, `hero_cta_url`, `hero_image_path`, `hero_alignment` à tabela `catalog_settings`
- [ ] 1.2 Atualizar `App\Models\CatalogSetting`: `#[Fillable]`, `casts()` (`hero_enabled => boolean`) e `defaults()` (`hero_enabled => false`, `hero_alignment => 'left'`)
- [ ] 1.3 Rodar migration e validar Pint/PHPStan

## 2. Painel — Filament

- [ ] 2.1 Em `ManageCatalogSettings`, adicionar `Section::make('Hero da home do catálogo')` antes de "Apresentação" com Toggle, TextInput, Textarea, FileUpload e Select conforme o design
- [ ] 2.2 Garantir que o gate `catalog.settings.manage` continua bloqueando Editor e Author

## 3. Frontend público

- [ ] 3.1 Criar componente `resources/views/components/site/catalog-hero.blade.php` (variantes com/sem imagem, alinhamento, CTA opcional)
- [ ] 3.2 Refatorar `resources/views/catalog/home.blade.php`: hero no topo (quando ativo), header com título+intro condicional, categorias em chips, busca rápida, destaques e últimos produtos com `section-heading`
- [ ] 3.3 Refinar `resources/views/catalog/_product-card.blade.php` (badge "Sob consulta", `loading="lazy"`, `decoding="async"`, tamanho dos botões)
- [ ] 3.4 Refinar `resources/views/catalog/index.blade.php` (header unificado, ordenação, filtros consistentes)
- [ ] 3.5 Refinar `resources/views/catalog/category.blade.php` (breadcrumb com chevron SVG, header padronizado)
- [ ] 3.6 Refinar `resources/views/catalog/show.blade.php` (CTA sticky mobile, info sticky desktop, acordeões independentes)

## 4. SEO

- [ ] 4.1 Ajustar `SeoService::forCatalogHome` para usar `hero_title` como fallback de `og:title` (e `meta.title` quando `seo_title` vazio)

## 5. Testes

- [ ] 5.1 Atualizar `tests/Feature/Filament/ManageCatalogSettingsTest.php` com cenário "Super Admin salva o hero"
- [ ] 5.2 Adicionar cenários em `tests/Feature/Catalog/PublicCatalogTest.php`: "Home renderiza hero quando habilitado", "Home oculta hero quando desabilitado", "CTA do hero aparece quando label e url preenchidos"

## 6. Validação final

- [ ] 6.1 `./scripts/format.sh` (Pint)
- [ ] 6.2 `composer analyse` (PHPStan)
- [ ] 6.3 `./scripts/test.sh` (PHPUnit)
- [ ] 6.4 `openspec validate 2026-07-28-improve-catalog-layout-hero --strict`
- [ ] 6.5 Arquivar a change via `openspec archive 2026-07-28-improve-catalog-layout-hero` após validação