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
