# Delta: <Domínio afetado>

Este arquivo descreve o **delta** da mudança em `openspec/specs/<dominio>/spec.md`. Use as seções abaixo conforme o tipo de alteração.

## Purpose

_(Obrigatório apenas quando a mudança introduz uma nova capacidade ainda sem `spec.md` principal. Caso a spec já exista, esta seção é IGNORADA no merge.)_

Resumo em 1–2 parágrafos do que esta nova capacidade representa.

## ADDED Requirements

_(Requisitos novos.)_

### Requirement: <Nome curto>
O sistema DEVE <comportamento observável>.

#### Scenario: <Cenário>
- **GIVEN** <estado inicial>
- **WHEN** <ação>
- **THEN** <resultado esperado>
- **AND** <resultado adicional opcional>

## MODIFIED Requirements

_(Requisitos existentes cujo comportamento muda. Use o header **exato** atual, normalizado, para que o merge localize a entrada.)_

### Requirement: <Nome exatamente igual ao da spec atual>
O sistema DEVE <novo comportamento>.

_(Previously: <comportamento antigo>.)_

#### Scenario: <Cenário>
- **GIVEN** <estado inicial>
- **WHEN** <ação>
- **THEN** <novo resultado esperado>

## REMOVED Requirements

_(Requisitos que deixam de existir.)_

### Requirement: <Nome exatamente igual ao da spec atual>
_(Motivo da remoção e caminho de migração, se aplicável.)_

## RENAMED Requirements

_(Apenas quando o nome do requisito muda sem mudar o conteúdo. Use quando nem `MODIFIED` nem `REMOVED` representam bem a mudança.)_

- FROM: `### Requirement: Nome Antigo`
- TO: `### Requirement: Nome Novo`

## Notes

_(Opcional: comentários para o revisor, exceções conhecidas, vínculos com tasks.)_