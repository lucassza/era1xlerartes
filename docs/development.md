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

## Isolamento do banco de testes

A suíte **precisa** rodar contra `era1x_ler_artes_test`. Três mecanismos garantem isso, e os três são necessários:

1. **`tests/bootstrap.php`** (referenciado por `bootstrap=` no `phpunit.xml`). O container `app` recebe `APP_ENV`, `DB_CONNECTION`, `DB_DATABASE` e afins como variáveis de ambiente reais do SO (bloco `environment:` do `docker-compose.yml`). O repositório de env do Laravel é imutável e consulta `getenv()` antes de `$_ENV`, então os `<env force="true">` do `phpunit.xml` eram silenciosamente ignorados. Este bootstrap reaplica via `putenv()` os mesmos valores já declarados no `phpunit.xml` — lendo do próprio `phpunit.xml`, para que as duas fontes não saiam de sincronia.

2. **Neutralização do cache de configuração.** Em produção `php artisan optimize` grava `bootstrap/cache/config.php`; com a configuração em cache o Laravel nunca avalia `env()`, e a suíte leria a conexão de produção congelada no cache. O bootstrap aponta `APP_CONFIG_CACHE`, `APP_ROUTES_CACHE` e `APP_EVENTS_CACHE` para um caminho inexistente, forçando os testes a montar a configuração a partir do ambiente.

3. **Guarda em `tests/TestCase::setUp()`.** Verifica a conexão configurada *e* o nome do banco realmente aberto (`DB::connection()->getDatabaseName()`), abortando com `LogicException` se não for o banco de teste. A guarda é incondicional de propósito: a versão anterior só rodava sob `app()->environment('testing')` e por isso se desligava exatamente no cenário em que o ambiente vazava para `production`.

Credenciais não são fixadas em `phpunit.xml` nem em `.env.testing`: `DB_TEST_USERNAME`/`DB_TEST_PASSWORD` caem para `DB_USERNAME`/`DB_PASSWORD` do ambiente, já que o banco de teste vive no mesmo servidor Postgres. O `APP_KEY` de `.env.testing` é descartável e serve apenas para a suíte.

Ao mexer em `phpunit.xml`, `tests/bootstrap.php` ou `tests/TestCase.php`, confirme antes e depois com um teste descartável que imprima `DB::selectOne('select current_database()')`.
