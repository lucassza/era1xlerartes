# Desenvolvimento

## Pré-requisitos

Docker, Docker Compose, Git e acesso ao repositório. PHP, Composer e Node são opcionais quando todo o fluxo é executado nos containers.

## Comandos

```bash
./scripts/setup.sh   # dependências e build inicial
./scripts/dev.sh     # servidor, fila, logs e Vite
./scripts/test.sh    # suíte PHPUnit/Laravel
./scripts/lint.sh    # Pint e PHPStan
./scripts/format.sh  # formatadores disponíveis
```

Consulte o README para portas e credenciais locais. Novas funcionalidades devem incluir testes apropriados e não devem depender de serviços externos em testes unitários.

## Filtro de palavrões

A lista padrão fica em `config/profanity.php`. Para desabilitar temporariamente (ex.: em ambiente local) defina `PROFANITY_FILTER_ENABLED=false` no `.env`. Edições na lista exigem revisão para reduzir falsos positivos; o filtro usa comparação por fronteira de palavra Unicode-aware (`/\b(palavra)\b/u`).
