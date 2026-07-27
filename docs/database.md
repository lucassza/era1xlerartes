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
