# Contribuindo

## Antes de começar

Leia [`AGENTS.md`](AGENTS.md) e a documentação em [`docs/`](docs/). Mantenha o escopo da contribuição limitado ao problema descrito e não altere regras de negócio sem autorização.

## Fluxo local

```bash
./scripts/setup.sh
./scripts/test.sh
./scripts/lint.sh
```

Use `./scripts/dev.sh` para iniciar o ambiente de desenvolvimento. Docker Compose é o caminho recomendado para PostgreSQL, Redis, MinIO e Mailpit.

## Commits e revisão

Use Conventional Commits em Inglês, por exemplo `fix: validate scheduled publication`. Explique a motivação, os impactos e os testes na descrição do pull request. Não envie `.env`, chaves, dumps ou artefatos de build.
