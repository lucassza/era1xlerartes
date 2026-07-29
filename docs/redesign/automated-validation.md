# Validação automatizada do redesign

## Comandos e objetivos

| Comando | Objetivo | Status nesta execução |
| --- | --- | --- |
| `php artisan test` | Executar toda a suíte PHPUnit, incluindo os fluxos públicos, newsletter e SEO. | Aprovado: 70 testes e 274 assertions. O PHPUnit emitiu apenas aviso ambiental ao gravar seu cache de resultados. |
| `vendor/bin/pint --test` | Verificar a formatação PHP sem modificar arquivos. | Aprovado: 156 arquivos verificados. |
| `vendor/bin/phpstan analyse` | Analisar estaticamente o código PHP e os testes. | Aprovado: 83 arquivos analisados, sem erros. |
| `npm run build -- --configLoader runner` | Gerar os assets Vite e validar o build do frontend. | Aprovado no container Node. O loader `runner` evita o cache temporário não gravável do Vite. |

Os quatro comandos terminaram com código `0` na validação final.
