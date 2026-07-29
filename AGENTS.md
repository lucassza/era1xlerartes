# Regras para agentes de IA

Este arquivo é a fonte de verdade para Claude Code, Codex, Cursor, OpenCode e TaskMaster neste projeto.

## Idioma e convenções

- Responda sempre em Português do Brasil.
- Mantenha explicações e documentação em Português do Brasil.
- Mantenha código, classes, métodos, funções e variáveis em Inglês.
- Escreva comentários de código em Português apenas quando agregarem contexto relevante.
- Use commits em Inglês no formato Conventional Commits (`feat:`, `fix:`, `docs:`, `chore:`, `refactor:`, `test:`).

## Segurança de mudança

- Não altere a arquitetura ou regras de negócio sem autorização explícita.
- Não remova código sem justificar a remoção e verificar seus consumidores.
- Preserve alterações existentes de outros colaboradores.
- Antes de mudanças amplas, descreva escopo, risco e plano.
- Nunca inclua segredos, credenciais ou arquivos `.env` no Git.
- Prefira mudanças pequenas, reversíveis e fáceis de revisar.

## Qualidade técnica

- Escreva código limpo, coeso, testável e sem duplicação desnecessária.
- Priorize SOLID, Clean Architecture quando compatível com a estrutura atual e boas práticas Laravel.
- Evite dependências novas sem justificar custo, manutenção e necessidade.
- Considere performance, segurança, acessibilidade e observabilidade.
- Não faça refatorações não relacionadas ao objetivo da tarefa.

## Stack deste projeto

- Laravel 13, PHP 8.5 e Filament 5.
- PostgreSQL 18, Redis 8 e MinIO/S3.
- Docker Compose, Nginx, Vite 8, Tailwind CSS 4 e Alpine.js 3.
- A stack efetiva é a definida em `composer.json`, `package.json` e `docker-compose.yml`.

## Fluxo obrigatório

1. Leia este arquivo e a documentação relevante em `docs/` antes de codificar.
2. Inspecione o código e os testes relacionados à tarefa.
3. Implemente a menor mudança suficiente, sem alterar regras não solicitadas.
4. Execute testes e ferramentas de qualidade adequados ao escopo.
5. Explique no final o que mudou, as validações executadas e qualquer limitação.

Comandos principais:

```bash
./scripts/setup.sh
./scripts/dev.sh
./scripts/test.sh
./scripts/lint.sh
./scripts/format.sh
```

## Referências

- Arquitetura: `docs/architecture.md`
- Desenvolvimento: `docs/development.md`
- Banco de dados: `docs/database.md`
- API e rotas: `docs/api.md`
- Deploy: `docs/deployment.md`
- Agentes e automações: `docs/agents.md`
- Tarefas versionadas: `tasks/` e `.taskmaster/`
