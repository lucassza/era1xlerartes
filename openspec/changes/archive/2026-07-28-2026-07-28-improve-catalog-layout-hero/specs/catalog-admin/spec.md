# Spec: Configurações do hero no painel administrativo

## ADDED Requirements

### Requirement: Admin configura o hero da home

A página `ManageCatalogSettings` SHALL exibir uma seção **"Hero da home
do catálogo"** no topo do formulário (antes de "Apresentação"),
contendo os campos:

- `hero_enabled` (Toggle, obrigatório, default `false`).
- `hero_title` (TextInput, maxLength 255).
- `hero_subtitle` (Textarea, rows 2).
- `hero_cta_label` (TextInput, maxLength 100).
- `hero_cta_url` (TextInput, url, maxLength 255).
- `hero_image_path` (FileUpload, disco `public`, pasta `catalog/hero`,
  restrição `image`, max 4 MB).
- `hero_alignment` (Select com `left` => "Esquerda", `center` => "Centro",
  default `left`).

A permissão necessária SHALL ser `catalog.settings.manage` (já existente).

#### Scenario: Super Admin salva o hero

- **WHEN** um Super Admin abre `/admin/catalog-settings`, preenche
  `hero_enabled=true`, `hero_title`, `hero_subtitle`, faz upload da
  imagem e salva
- **THEN** o registro singleton de `catalog_settings` é atualizado e
  a home pública passa a renderizar o hero configurado

#### Scenario: Editor continua sem acesso

- **WHEN** um Editor abre `/admin/catalog-settings`
- **THEN** recebe HTTP 403 (gate `catalog.settings.manage`)

#### Scenario: Toggle desativado zera o hero na home

- **WHEN** o admin salva o formulário com `hero_enabled=false`
- **THEN** o frontend não renderiza o componente `<x-site.catalog-hero>`,
  mesmo que `hero_title`, `hero_image_path` etc. ainda estejam preenchidos

### Requirement: Cache de settings é invalidado ao salvar o hero

`CatalogObserver` SHALL continuar invalidando a chave
`catalog:settings:current` em qualquer `save()` de `CatalogSetting`,
incluindo alterações feitas na nova seção Hero.

#### Scenario: Alteração do hero aparece imediatamente na home

- **WHEN** o admin altera o hero e salva
- **THEN** a próxima requisição à `/` reflete o novo hero sem
  intervenção manual em cache