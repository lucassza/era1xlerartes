# Era1x Ler Artes

Blog moderno (fork do TDB Mix Blog) estendido com catálogo de produtos, construído com **Laravel**, **Filament**, **PostgreSQL**, **Redis**, **MinIO**, **Blade**, **Tailwind CSS** e **Docker Compose**.

## Stack

- PHP ^8.3
- Laravel 13
- Filament 5
- Livewire 4
- PostgreSQL 18
- Redis 8
- MinIO (S3-compatible)
- Nginx
- Vite 8
- Tailwind CSS 4
- Alpine.js 3

## Recursos implementados

- painel administrativo em `/admin`
- autenticação administrativa
- ACL com Spatie Permission
- proteção do último Super Admin
- auditoria com Spatie Activitylog
- CRUD base de:
  - posts
  - autores
  - categorias
  - tags
- estados editoriais:
  - draft
  - scheduled
  - published
  - archived
- publicação agendada por scheduler
- busca PostgreSQL com:
  - full text search
  - `pg_trgm`
  - `unaccent`
- mídia em MinIO/S3 com Media Library
- conversões WebP e thumbs responsivos
- sitemap XML
- RSS
- JSON-LD `BlogPosting`
- newsletter com inscrição, confirmação e cancelamento
- posts relacionados
- posts populares por `views_count`
- importador inicial do TDBMix (Blogger sitemap + parsing HTML)

## Estrutura do projeto

```text
app/
├── Console/
├── DataTransferObjects/
├── Enums/
├── Exceptions/
├── Filament/
├── Http/
├── Mail/
├── Media/
├── Models/
├── Policies/
├── Providers/
└── Services/

config/
database/
docker/
resources/
routes/
tests/
```

## Requisitos locais

Você precisa de:

- Docker + Docker Compose
- Git

Opcionalmente, para rodar sem Docker:

- PHP 8.5 com extensões compatíveis
- Composer 2
- Node 24+
- PostgreSQL 18+
- Redis 8+
- MinIO ou outro S3-compatible

## Instalação com Docker

### 1. Clonar o projeto

```bash
git clone <repo-url>
cd era1xlerartes
```

### 2. Subir a stack

```bash
docker compose up -d --build
```

### 3. Criar ambiente local

Se necessário:

```bash
cp .env.example .env
```

### 4. Gerar chave da aplicação

```bash
docker compose exec app php artisan key:generate
```

### 5. Rodar migrations

```bash
docker compose exec app php artisan migrate --force
```

### 6. Popular dados iniciais

```bash
docker compose exec app php artisan db:seed --force
```

### 7. Build do frontend

```bash
docker run --rm -v "$PWD:/app" -w /app node:24-bookworm-slim npm install
docker run --rm -v "$PWD:/app" -w /app node:24-bookworm-slim npm run build
```

## Acesso local

- aplicação pública: `http://localhost:8080`
- painel Filament: `http://localhost:8080/admin`
- Mailpit: `http://localhost:8025`
- MinIO Console: `http://localhost:9001`

## Credencial administrativa padrão

Após rodar o seeder:

- e-mail: `admin@example.com`
- senha: `password`

## Docker services

O `docker-compose.yml` sobe:

- `app`
- `nginx`
- `postgres`
- `redis`
- `minio`
- `minio-init`
- `mailpit`

Todos os serviços principais possuem healthcheck.

## Variáveis importantes

### Aplicação

- `APP_NAME`
- `APP_ENV`
- `APP_DEBUG`
- `APP_URL`

### PostgreSQL

- `DB_CONNECTION=pgsql`
- `DB_HOST=postgres`
- `DB_PORT=5432`
- `DB_DATABASE`
- `DB_USERNAME`
- `DB_PASSWORD`

### Redis

- `REDIS_CLIENT=phpredis`
- `REDIS_HOST=redis`
- `REDIS_PASSWORD`
- `CACHE_STORE=redis`
- `QUEUE_CONNECTION=redis`
- `SESSION_DRIVER=redis`

### MinIO / S3

- `FILESYSTEM_DISK=s3`
- `MEDIA_DISK=s3`
- `MEDIA_CONVERSIONS_DISK=s3`
- `AWS_ACCESS_KEY_ID`
- `AWS_SECRET_ACCESS_KEY`
- `AWS_BUCKET`
- `AWS_ENDPOINT`
- `AWS_URL`
- `AWS_USE_PATH_STYLE_ENDPOINT=true`
- `AWS_VISIBILITY=private`

### Media Library

- `IMAGE_DRIVER=imagick`
- `MEDIA_QUEUE=media`
- `QUEUE_CONVERSIONS_BY_DEFAULT=true`
- `QUEUE_CONVERSIONS_AFTER_DB_COMMIT=true`

## PostgreSQL

O projeto depende de PostgreSQL como banco principal.

### Recursos usados

- UUID
- índices
- foreign keys
- full text search
- `pg_trgm`
- `unaccent`
- `search_tsv`
- trigger de indexação para posts

### Extensões necessárias

As migrations já criam quando possível:

- `pgcrypto`
- `pg_trgm`
- `unaccent`

## Redis

Redis é usado para:

- cache
- filas
- sessões
- cache de permissões do Spatie

## MinIO

Todo o storage de mídia está preparado para S3-compatible MinIO.

### Estrutura de paths

- `blog/posts/{uuid}/`
- `blog/categories/{uuid}/`
- `blog/authors/{uuid}/`

### Coleções configuradas

- `Post` → `cover`
- `Category` → `image`
- `Author` → `avatar`

### Conversões

- `webp`
- `thumb-300`
- `thumb-600`
- `thumb-1200`

## ACL

ACL implementada com `spatie/laravel-permission`.

### Roles seedadas

- `Super Admin`
- `Editor`
- `Author`

### Regras sensíveis já implementadas

- proteção do último `Super Admin`
- bloqueio de exclusão da role `Super Admin`
- sincronização segura de papéis por service

### Policies já existentes

- `PostPolicy`
- `AuthorPolicy`
- `CategoryPolicy`
- `TagPolicy`
- `UserPolicy`
- `RolePolicy`
- `PermissionPolicy`

## Filament

Painel principal:

- path: `/admin`

Recursos atuais:

- dashboard
- settings
- posts
- authors
- categories
- tags
- users
- roles
- permissions

## Scheduler e filas

### Publicação agendada

Comando:

```bash
docker compose exec app php artisan posts:publish-scheduled
```

Agendado em `routes/console.php` para rodar a cada minuto.

### Worker

Para desenvolvimento manual:

```bash
docker compose exec app php artisan queue:work
```

## Importador TDBMix

O projeto já possui infraestrutura para importar conteúdo do Blogger/TDBMix.

### Comando

```bash
docker compose exec app php artisan tdbmix:import-posts --dry-run --limit=3
```

### Opções disponíveis

- `--dry-run`
- `--limit=`
- `--url=`

### O que já faz

- lê o sitemap
- busca o HTML do post
- extrai título, conteúdo, tags, data, imagem destacada
- cria/atualiza posts por `source_url`
- cria tags
- cria author padrão de importação
- importa imagem destacada para a collection `cover`

## Newsletter

Estrutura pronta para:

- inscrição pública
- confirmação por link assinado
- cancelamento por link assinado

Rotas:

- `POST /newsletter/subscribe`
- `GET /newsletter/confirm/{subscriber}`
- `GET /newsletter/unsubscribe/{subscriber}`

## Desenvolvimento

### Script principal

```bash
composer run dev
```

### Qualidade de código

```bash
docker compose exec app vendor/bin/pint --test
docker compose exec app vendor/bin/phpstan analyse --memory-limit=1G
docker compose exec app php artisan test
```

## Testes

Rodar todos:

```bash
docker compose exec app php artisan test
```

Rodar testes específicos:

```bash
docker compose exec app php artisan test --filter=PostSearchTest
docker compose exec app php artisan test --filter=MediaLibraryIntegrationTest
docker compose exec app php artisan test --filter=NewsletterFlowTest
docker compose exec app php artisan test --filter=LastSuperAdminProtectionTest
```

## Produção

### Checklist mínimo

- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL` correto
- SMTP real configurado
- bucket MinIO/S3 persistente
- PostgreSQL persistente
- Redis persistente e protegido
- HTTPS ativo
- queue worker rodando continuamente
- scheduler rodando continuamente

### Itens importantes

- `AppServiceProvider` força HTTPS em produção
- o painel admin depende de `admin.access`
- a publicação agendada depende do scheduler
- a busca depende das extensões PostgreSQL já migradas

## Backup

Ainda não existe rotina automatizada de backup no repositório.

Hoje o que deve ser protegido em backup é:

1. banco PostgreSQL
2. bucket MinIO/S3
3. `.env` seguro

### Sugestão mínima

- dump diário do PostgreSQL
- snapshot/replicação do bucket de mídia
- retenção por data

## Deploy

Ainda não existe pipeline CI/CD versionado no projeto.

### Fluxo manual recomendado

1. atualizar código
2. instalar dependências
3. rodar migrations
4. build de frontend
5. limpar caches
6. reiniciar workers

Exemplo:

```bash
docker compose exec app php artisan migrate --force
docker compose exec app php artisan optimize:clear
docker compose exec app php artisan queue:restart
docker run --rm -v "$PWD:/app" -w /app node:24-bookworm-slim npm run build
```

## Seeds

### Seeder principal

```bash
docker compose exec app php artisan db:seed --force
```

### O que ele cria hoje

- permissões
- roles
- usuário `admin@example.com`
- categorias
- tags
- autores
- 50 posts fictícios

## Limitações atuais

Alguns itens do briefing original ainda podem ser refinados no futuro, como:

- dashboard administrativo mais avançado
- analytics real de posts populares
- importação em lote completa do TDBMix com revisão editorial
- refinamentos finais de produção e backup automatizado

## Licença

O código da aplicação está estruturado para ser distribuído como projeto Laravel open source.

## Trabalho com agentes

Todos os agentes devem seguir [AGENTS.md](AGENTS.md). Consulte [docs/agents.md](docs/agents.md) para as integrações de Claude Code, Codex, Cursor, OpenCode e OpenSpec.

## Qualidade e contribuição

Use `scripts/test.sh`, `scripts/lint.sh` e `scripts/format.sh` antes de abrir uma contribuição. O processo está detalhado em [CONTRIBUTING.md](CONTRIBUTING.md).
