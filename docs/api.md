# API e rotas

As rotas web públicas estão em `routes/web.php` e as rotas de console em `routes/console.php`. Controllers devem permanecer finos, delegando regras para serviços e validando entradas com as ferramentas do Laravel.

Antes de adicionar uma API HTTP, defina contrato, autenticação, autorização, formato de erro, paginação e testes. Registre endpoints novos nesta página ou na documentação específica da feature.

## Autenticação do site

| Método | URI | Nome | Middleware | Descrição |
|---|---|---|---|---|
| `GET` | `/entrar` | `site.login` | `guest` | Formulário de login do público |
| `POST` | `/entrar` | `site.login.store` | `guest` | Autentica e redireciona |
| `GET` | `/cadastro` | `site.register` | `guest` | Formulário de cadastro |
| `POST` | `/cadastro` | `site.register.store` | `guest` | Cria usuário com `type = site` e dispara verificação de e-mail |
| `GET` | `/email/verify` | `verification.notice` | `auth` | Tela "verifique seu e-mail" |
| `GET` | `/email/verify/{id}/{hash}` | `verification.verify` | `auth`, `signed` | Confirma o e-mail |
| `POST` | `/email/verification-notification` | `verification.send` | `auth`, `throttle:6,1` | Reenvia o e-mail |
| `POST` | `/sair` | `site.logout` | `auth` | Faz logout da sessão do site |
| `GET` | `/perfil` | `site.profile.edit` | `auth` | Exibe o perfil público autenticado |
| `PUT` | `/perfil` | `site.profile.update` | `auth` | Atualiza nome, e-mail e cidade do usuário autenticado |
| `POST` | `/perfil/avatar` | `site.profile.avatar.update` | `auth`, `throttle:10,1` | Atualiza o avatar do usuário autenticado |
| `DELETE` | `/perfil/avatar` | `site.profile.avatar.destroy` | `auth` | Remove o avatar do usuário autenticado |
| `PUT` | `/perfil/senha` | `site.profile.password.update` | `auth`, `throttle:6,1` | Atualiza a senha do usuário autenticado |

## Engajamento

| Método | URI | Nome | Middleware | Descrição |
|---|---|---|---|---|
| `GET` | `/depoimentos` | `testimonials.index` | `engagement:testimonials` | Lista depoimentos aprovados |
| `POST` | `/depoimentos` | `testimonials.store` | `auth`, `verified`, `engagement:testimonials` | Envia depoimento (moderação) |
| `POST` | `/posts/{post}/comentarios` | `posts.comments.store` | `auth`, `verified`, `engagement:comments` | Envia comentário com nota (moderação) |

O middleware `engagement` aborta com `404` quando a flag global correspondente em `blog_settings` está desabilitada. Já o override por post (`posts.comments_locked`) é tratado dentro de `EngagementService::submitComment`, que retorna `403`.

## Catálogo público

A home (`/`) deixou de listar artigos do blog e passou a exibir o catálogo público. As URLs existentes do blog foram preservadas e a listagem foi movida para `/blog`.

| Método | URI | Nome | Middleware | Descrição |
|---|---|---|---|---|
| `GET` | `/` | `home` | — | Home do catálogo (banner, intro, categorias, busca, destaques, últimos) |
| `GET` | `/produtos` | `products.index` | — | Listagem filtrada (categoria, marca, disponibilidade, faixa de preço, destaque) com ordenação |
| `GET` | `/produtos/{slug}` | `products.show` | — | Detalhe do produto (galeria com lightbox Alpine, atributos, anexos, relacionados, JSON-LD, OG/Twitter, BreadcrumbList) |
| `POST` | `/produtos/{slug}/consulta` | `products.inquiries.store` | — | Submete consulta pública (honeypot, consentimento LGPD, rate-limit por IP, snapshot do produto, fila de notificações). Retorna `302` para o back com `inquiry_status=ok` ou `201` com JSON para `Accept: application/json`. Bloqueia com `404` quando o produto não existe ou está em rascunho/indisponível. |
| `GET` | `/categorias/{slug}` | `catalog-categories.show` | — | Listagem hierárquica por categoria (inclui subcategorias), com BreadcrumbList JSON-LD |
| `GET` | `/blog` | `blog.index` | — | Listagem de artigos (mesma view `welcome` da home antiga) |
| `GET` | `/contato` | `site.contact` | — | Página de contato com telefone/WhatsApp/e-mails vindos de `catalog_settings` |

Pesquisa via `/search?q=` continua existindo para artigos do blog. Páginas com `?buscar=` na listagem do catálogo recebem `robots: noindex,follow`.

## SEO

| Método | URI | Nome | Middleware | Descrição |
|---|---|---|---|---|
| `GET` | `/sitemap.xml` | `seo.sitemap` | — | Sitemap XML (home, `/produtos`, `/produtos/{slug}` para publicados, `/categorias/{slug}` ativas, além de `/posts/{slug}`, `/categories/{slug}`, `/tags/{slug}`, `/authors/{slug}` do blog) |
| `GET` | `/rss.xml` | `seo.rss` | — | Feed RSS do blog (somente artigos publicados — sem produtos) |
| `GET` | `/robots.txt` | `seo.robots` | — | `User-agent: *`, `Allow: /`, `Disallow: /admin \| /perfil \| /dashboard \| /depoimentos` + `Sitemap: <url>` |

Meta tags e JSON-LD:
- Home: `WebSite` JSON-LD, OG/Twitter `website`.
- Blog post: `BlogPosting` + `BreadcrumbList` JSON-LD, `og:type=article`, `twitter:summary_large_image`.
- Produto: `Product` (+ `Brand` opcional) + `BreadcrumbList` JSON-LD, `og:type=product`, `twitter:summary_large_image` com imagem de capa.
- Categoria de catálogo: `BreadcrumbList` JSON-LD, `og:type=website`.
- Consulta pública (`/produtos/{slug}/consulta`): resposta `201` em JSON com `status`, `message`, `inquiry_id`. Resposta HTML retorna `302` com flash `inquiry_status=ok`.