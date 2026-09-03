# blog-navigation Specification

## Purpose
TBD - created by archiving change add-product-catalog. Update Purpose after archive.
## Requirements
### Requirement: Menu principal reorganizado

A navegação pública SHALL conter, no mínimo: Início (`/`), Produtos
(`/produtos`), Categorias (categorias do catálogo), Blog (`/blog`) e Contato.
O menu SHALL ser gerado pelo `PublicNavigationService` estendido e refletido
no header, footer e navegação mobile.

#### Scenario: Menu no desktop e mobile

- **WHEN** um visitante abre qualquer página pública
- **THEN** vê as entradas Início, Produtos, Categorias, Blog e Contato, com
  destaque na seção ativa, em desktop e mobile

### Requirement: Texto do rodapé editável no Admin

O texto exibido abaixo da logo no rodapé SHALL vir de `blog_settings.footer_text`,
editável em `/admin/settings` por quem tem a permissão `settings.manage`.
Quando o campo estiver em branco, o parágrafo NÃO SHALL ser renderizado.

#### Scenario: Admin altera o texto do rodapé

- **GIVEN** um admin na seção "Rodapé" de `/admin/settings`
- **WHEN** altera `footer_text` e salva
- **THEN** o texto abaixo da logo no rodapé público reflete o novo valor

#### Scenario: Texto em branco oculta o parágrafo

- **GIVEN** um admin na seção "Rodapé" de `/admin/settings`
- **WHEN** deixa `footer_text` em branco e salva
- **THEN** o parágrafo abaixo da logo deixa de ser renderizado

### Requirement: Listagem do blog em `/blog`

A rota `/blog` SHALL exibir a listagem paginada de artigos publicados,
assumindo o papel que a home tinha, com SEO próprio.

#### Scenario: Visitante acessa o blog pelo menu

- **WHEN** o visitante clica em "Blog" no menu
- **THEN** acessa `/blog` e vê os artigos publicados paginados

### Requirement: Preservação das URLs do blog

As rotas `/posts/{slug}`, `/categories/{slug}`, `/tags/{slug}`,
`/authors/{slug}`, `/rss.xml` e `/sitemap.xml` SHALL continuar funcionando
exatamente como antes, sem redirecionamentos.

#### Scenario: Artigo existente acessível

- **WHEN** um visitante acessa uma URL de artigo publicada antes da mudança
- **THEN** a página é exibida normalmente com status 200

#### Scenario: RSS contém apenas artigos

- **WHEN** um leitor acessa `/rss.xml`
- **THEN** o feed contém somente artigos do blog, sem produtos

### Requirement: SEO do catálogo e sitemap unificado

O `sitemap.xml` SHALL incluir produtos publicados e categorias de catálogo
além dos artigos; a home do catálogo SHALL ter canonical, title, meta
description, Open Graph, Twitter Cards e breadcrumbs configuráveis; `robots`
SHALL permanecer configurável.

#### Scenario: Sitemap com produtos

- **WHEN** o sitemap é gerado
- **THEN** inclui URLs de `/produtos/{slug}` publicados e `/categorias/{slug}`
  ativas, além das URLs do blog já existentes

