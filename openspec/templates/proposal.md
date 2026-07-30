# Proposal: <Nome da mudança em kebab-case>

## Intent

Por que esta mudança é necessária? Qual problema ou oportunidade ela resolve? Mantenha em 1–3 parágrafos curtos.

## Scope

Liste em bullets o que está dentro do escopo:

- Item 1.
- Item 2.

E, se aplicável, liste explicitamente o que está fora do escopo para evitar ambiguidade:

- Fora: item X.

## Approach

Resumo técnico em alto nível. Detalhes vão em `design.md`.

- Decisão arquitetural 1.
- Decisão arquitetural 2.
- Integrações externas afetadas.

## Affected domains

Marque os domínios cujo comportamento muda:

- [ ] `blog-navigation`
- [ ] `catalog-acl`
- [ ] `catalog-admin`
- [ ] `catalog-public`
- [ ] `inquiry-admin`
- [ ] `product-inquiry`

## Impact

- Breaking change? (sim/não) — descreva quem é afetado.
- Necessita migração de dados? (sim/não) — descreva.
- Necessita feature flag? (sim/não) — descreva.
- Impacto em SEO / cache / filas? — descreva.

## Risks

Liste riscos principais e mitigações:

- Risco 1 → mitigação.
- Risco 2 → mitigação.

## Plan

Passos resumidos em ordem. Detalhes em `tasks.md`.

1. Passo 1.
2. Passo 2.
3. Passo 3.

## Validation

Como será validado antes do `/opsx:archive`:

- `./scripts/test.sh` — testes automatizados.
- `./scripts/lint.sh` — estilo e análise estática.
- Validação manual em rota X.
- Checklist de QA em Y.

## Authorization

Mudanças amplas (arquitetura, ACL, regras de negócio) DEVEM registrar autorização explícita:

- Solicitado por: <pessoa/equipe>.
- Aprovado por: <pessoa/equipe>.
- Data: YYYY-MM-DD.
