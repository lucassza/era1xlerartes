# Era1x Ler Artes — Instruções para Agentes

Este arquivo é a fonte de verdade para Claude Code, Cursor, Codex e OpenCode neste projeto. Arquivos específicos de cada ferramenta (`CLAUDE.md`, `.cursor/rules/`, `opencode.json`) referenciam este documento e não devem duplicar seu conteúdo.

> Este projeto é um fork do `tdb-mix-blog` estendido com um catálogo de produtos. A identidade em código (branding Filament, views, banco de teste, containers) já foi corrigida para "Era1x Ler Artes" — ver `## Pendências herdadas do fork` abaixo para o que ainda resta e por quê.

## Ordem de consulta

Quando houver conflito entre fontes, siga esta precedência:

1. `openspec/specs/` — comportamento atual, fonte de verdade do produto.
2. `openspec/changes/<nome>/` — mudança em andamento, se houver.
3. Este arquivo (`AGENTS.md`) — regras gerais.
4. `docs/` — arquitetura e operação.
5. `.cursor/rules/`, `CLAUDE.md`, `opencode.json` — apenas adaptações por ferramenta.

## Contexto do projeto

- **Produto**: blog editorial (herdado do TDB Mix Blog) + catálogo de produtos (exposição e consulta comercial via formulário de interesse — sem carrinho, checkout ou pagamento).
- **Backend**: PHP `^8.3`, Laravel `13.21` (`composer.json`/`composer.lock`), Filament `5.7` ("Era1x Ler Artes" no painel admin, cor primária `#650010`), Livewire `4.3` (somente no painel admin).
- **Persistência**: PostgreSQL (padrão `pgsql` em `config/database.php`) com busca full-text via `search_tsv`.
- **Cache/filas/sessão**: Redis (padrão em `config/{queue,cache,session}.php`).
- **Storage**: S3-compatível (MinIO) via `league/flysystem-aws-s3-v3` + `spatie/laravel-medialibrary`.
- **Frontend**: Blade + Alpine.js `^3`, Vite `^8`, Tailwind CSS `4`. Sem Vue, React ou TypeScript.
- **ACL**: `spatie/laravel-permission ^7`, permissões `recurso.acao` (inclui `catalog.*`), guard único `web` (sessão). Sem multitenancy, sem broadcasting/WebSockets.
- **Domínios** (= capabilities em `openspec/specs/`): `blog-navigation`, `catalog-acl`, `catalog-admin`, `catalog-public`, `inquiry-admin`, `product-inquiry`.
- **Docker**: `app`, `nginx`, `postgres`, `redis`, `minio`/`minio-init`, `mailpit` (`docker-compose.yml`).
- A stack efetiva é sempre a definida em `composer.json`, `package.json`, `config/*.php` e `docker-compose.yml` — este arquivo resume, não substitui.

## Pendências herdadas do fork

Corrigido: `container_name` dos 7 serviços em `docker-compose.yml` (agora `era1x-ler-artes-*`), banco de teste (`phpunit.xml`, `tests/TestCase.php`, `docker/postgres/initdb/01-create-test-db.sql`, `config/database.php` → `era1x_ler_artes_test`), `.env.example`/`.env.testing` (`DB_DATABASE`, `DB_USERNAME`, `CACHE_PREFIX`, `REDIS_PREFIX`, `SESSION_COOKIE`, `APP_NAME`), `public/site.webmanifest`, `README.md` (título, PHP version, instrução `cd`), `tests/Feature/Filament/AdminBrandingTest.php` (estava testando branding antigo — o código real já usava "Era1x Ler Artes"/`#650010`, só o teste ficou desatualizado), o texto visível em `resources/views/components/ui/card.blade.php`, e o prefixo das variáveis CSS `--tdb-*` → `--era1x-*` em `resources/css/app.css`, `resources/css/filament/theme.css` e `resources/css/app-classic-backup.css` (só o nome, os valores de cor já estavam corretos; nenhum outro arquivo referenciava essas variáveis).

**Ainda pendente, não corrigido — decisão explícita do usuário foi adiar**:

- `docker-compose.yml` linha 1 (`name: tdb-mix-blog`) e os defaults `DB_DATABASE`/`POSTGRES_DB`/`POSTGRES_USER` (`tdb_mix_blog`/`tdb`): o ambiente Docker está **rodando ao vivo** com esses nomes. `name:` no topo do compose define o namespace dos volumes (`tdb-mix-blog_postgres-data`, etc.) — trocá-lo sem migrar os volumes tornaria os dados atuais órfãos. O `.env` real também usa `DB_DATABASE=tdb_mix_blog`/`DB_USERNAME=tdb` para o banco de dev em uso — mudar isso é uma operação de dados (renomear banco Postgres ao vivo, migrar volume), não uma edição de config. Fazer com cautela quando houver uma janela de manutenção.
- `database/migrations/2026_07_23_080000_create_blog_settings_table.php`: coluna `site_name` tem `->default('TDB Mix Blog')`. Migration já aplicada em produção/dev — mudar o default aqui não afeta linhas existentes; ajustar o valor real exigiria uma migration nova ou um `UPDATE`, não uma edição no arquivo já rodado.
- Menções a "TDBMix"/"TdbMix" em `app/Services/TdbMixImportService.php`, `app/Services/TdbMixPostParser.php`, `ImportTdbMixPostsCommand`, e no `README.md` (seção "Importador TDBMix") — **isso não é identidade residual**: é o nome real de uma feature que importa conteúdo do blog original (Blogger/TDB Mix) para este sistema. Não renomear.
- `docs/redesign/dependency-decision.md` e `docs/redesign/final-audit.md`: documentos históricos de uma sessão de redesign anterior à identidade atual — preservados como registro, não como bug.

## Idioma e convenções

- Responda sempre em Português do Brasil.
- Mantenha explicações, documentação, specs e relatórios em Português do Brasil.
- Identificadores de código (classes, métodos, funções, variáveis) em Inglês — convenção já usada em todo o projeto, incluindo o domínio de catálogo (`Brand`, `CatalogCategory`, `Product`, `ProductInquiry`). Não traduza identificadores existentes.
- Escreva comentários de código em Português apenas quando agregarem contexto relevante.
- Commits em Inglês no formato Conventional Commits (`feat:`, `fix:`, `docs:`, `chore:`, `refactor:`, `test:`).

## Regras obrigatórias

- Leia o código e os testes relacionados antes de propor mudanças.
- Consulte `openspec/specs/` e `openspec/changes/` antes de alterar comportamento.
- Não altere arquitetura, ACL ou regras de negócio sem autorização explícita.
- Não remova código sem justificar a remoção e verificar seus consumidores.
- Não duplique funcionalidades já existentes em `app/Services/`.
- Não altere contratos de API sem registrar a mudança via OpenSpec.
- Preserve alterações existentes de outros colaboradores.
- Nunca inclua segredos, credenciais ou arquivos `.env` no Git.
- Prefira mudanças pequenas, reversíveis e fáceis de revisar.
- Evite refatorações não relacionadas ao objetivo da tarefa.
- `CategoryPolicy` e `TagPolicy` não estão registradas em `AuthServiceProvider::$policies` (dependem de auto-discovery).

## Processo de trabalho

**Antes de implementar:**

1. Compreenda a solicitação e localize os arquivos envolvidos.
2. Identifique o domínio afetado (blog ou catálogo) em `openspec/specs/`.
3. Consulte `openspec/changes/` por uma change ativa relacionada.
4. Identifique riscos (migrations, permissões `catalog.*`, contratos de rota).
5. Proponha um plano; implemente somente o escopo solicitado.

**Depois de implementar:**

1. Rode `./scripts/format.sh` (Pint + Prettier, quando instalado).
2. Rode `./scripts/lint.sh` (Pint --test + PHPStan).
3. Rode `./scripts/test.sh` (PHPUnit) — ciente da pendência de banco de teste acima.
4. Revise o diff e verifique impacto em segurança e autorização.
5. Atualize `docs/` e `openspec/specs/` quando o comportamento mudar.
6. Relate: arquivos alterados, comandos executados, testes que não puderam rodar e por quê.

## Restrições

- Não execute comandos destrutivos sem necessidade (sem `migrate:fresh` fora de ambiente de teste).
- Não apague dados nem recrie o banco indiscriminadamente.
- Não altere arquivos `.env` com segredos nem inclua credenciais no repositório.
- Não modifique dependências (`composer.json`/`package.json`) sem justificativa.
- Não ignore erros de teste nem esconda falhas.
- Não marque tarefa como concluída sem validação (testes + lint executados).

## Comandos do projeto

```bash
composer setup    # install, .env, key:generate, migrate --force, npm install, npm run build
composer dev      # serve + queue:listen + pail + vite (via concurrently)
composer test     # config:clear + artisan test
composer lint     # pint --test
composer analyse  # phpstan analyse --memory-limit=1G

./scripts/setup.sh
./scripts/dev.sh
./scripts/test.sh
./scripts/lint.sh
./scripts/format.sh

docker compose up -d
```

## Fluxo OpenSpec

Mudanças de contrato, ACL, regras de negócio, fluxos críticos ou arquitetura DEVEM ser planejadas via OpenSpec em `openspec/changes/<nome>/` antes de tocar em código. Use `/opsx-*` (Cursor/OpenCode) ou `/opsx:*` (Claude Code) — ver `openspec/config.yaml` para contexto e regras deste projeto.

1. Change ativa em `openspec/changes/` → `/opsx-apply` / `/opsx:apply` ou `/opsx-continue`.
2. Pedido ambíguo → `/opsx-explore` primeiro.
3. Pedido claro → `/opsx-propose` → `/opsx-apply` → `/opsx-verify` → `/opsx-archive`.

**Exigido para**: novas funcionalidades, mudança de regra de negócio, alteração de banco, mudança de contrato de API, mudança de autenticação/permissões, integrações externas, mudanças arquiteturais ou que afetem múltiplos domínios.

**Fluxo simplificado permitido para**: perguntas e explicações, typos e formatação, correções ortográficas, ajuste trivial em 1 arquivo sem mudança comportamental, ou pedido explícito do usuário para pular. Mesmo assim, registre problema, solução e testes executados na resposta final.

Templates disponíveis em `openspec/templates/` (`proposal.md`, `design.md`, `tasks.md`, `spec.md`).

## Execução de tarefas

`openspec/` define **o que** deve ser feito. A execução do dia a dia é acompanhada no **Task Manager do CloudCLI**, que lê `.taskmaster/tasks/tasks.json` deste projeto (formato do `task-master-ai`, CLI `task-master`).

- Ao decompor uma change em tarefas, espelhe `tasks.md` em `.taskmaster/tasks/tasks.json`.
- Status suportados: `pending`, `in-progress`, `done`, `review`, `blocked`, `deferred`, `cancelled`.
- OpenSpec define o quê; o Task Manager acompanha a execução. Nenhum dos dois substitui o outro.

## Distribuição entre agentes

Recomendação, não limitação rígida:

- **Claude Code**: análise arquitetural, planejamento, mudanças grandes, investigação de regra de negócio, documentação, features complexas.
- **Cursor**: desenvolvimento interativo, alterações pontuais, edição rápida, navegação entre arquivos, correções durante o desenvolvimento.
- **Codex**: revisão independente, implementação bem delimitada, correção de bugs, criação de testes, validação de diffs, análise de segurança e regressões.
- **OpenCode**: orquestração de múltiplas tarefas, workflows automatizados, divisão de tarefas, execução sequencial, revisão, testes, refatoração controlada.

## Referências

- Arquitetura: `docs/architecture.md`
- Desenvolvimento: `docs/development.md`
- Banco de dados: `docs/database.md`
- API e rotas: `docs/api.md`
- Deploy: `docs/deployment.md`
- Agentes e automações: `docs/agents.md`
- Tarefas versionadas (notas curtas, não substituem OpenSpec): `tasks/README.md`
- Mudanças comportamentais: `openspec/` (specs, changes, config.yaml)
- Execução: `.taskmaster/` (Task Manager do CloudCLI)
