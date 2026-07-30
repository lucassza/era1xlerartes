# Design: <Nome da mudança>

## Visão técnica

Descreva a abordagem técnica escolhida e por que ela é a melhor dentro das restrições do projeto. Mantenha foco em decisões, não em tutorial.

## Decisões

### Decisão 1: <Título>

- Contexto.
- Opções consideradas.
- Escolha e justificativa.
- Trade-offs.

### Decisão 2: <Título>

- Contexto.
- Opções consideradas.
- Escolha e justificativa.
- Trade-offs.

## Componentes afetados

Liste arquivos, classes, services, migrations, rotas e policies tocados pela mudança.

- `app/...` — papel na mudança.
- `database/migrations/...` — papel na mudança.
- `routes/...` — papel na mudança.

## Dados

- Mudanças de schema? Liste tabelas e colunas afetadas.
- Necessidade de backfill? Como será executado e validado.
- Estratégia para feature flag (se aplicável).

## Segurança e performance

- Superfície de ataque alterada?
- Autorização adicional necessária (permissões `catalog.*` / `blog.*`)?
- Índices/queries afetados? Risco de N+1?
- Cache invalidado por quê?

## Compatibilidade

- Quebra contrato público? Como migrar consumidores.
- Compatibilidade com Filament, Spatie, Livewire, Tailwind.

## Observabilidade

- Logs estruturados adicionados.
- Métricas alteradas.
- Auditoria via Spatie Activitylog.

## Plano de rollback

- Como reverter com segurança.
- Dados que precisam de cleanup manual.
