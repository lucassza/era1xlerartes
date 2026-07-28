# inquiry-admin Specification

## Purpose
TBD - created by archiving change add-product-catalog. Update Purpose after archive.
## Requirements
### Requirement: Listagem de consultas

O painel SHALL listar consultas com produto (nome em snapshot), interessado,
telefone, e-mail, data, status, responsável, origem e indicador de não lida,
com pesquisa, filtros (período, produto, categoria, status, responsável,
origem), ordenação e paginação. Consultas novas SHALL aparecer destacadas.

#### Scenario: Consulta nova destacada

- **WHEN** existe consulta com status `new` ainda não visualizada
- **THEN** ela aparece com indicador visual de não lida na listagem

#### Scenario: Filtro combinado

- **WHEN** o usuário filtra por status `in_service` e um produto específico
- **THEN** a listagem exibe apenas as consultas correspondentes, paginadas

### Requirement: Visualização completa da consulta

Ao abrir uma consulta, SHALL exibir dados completos do interessado, produto
consultado (com link para o produto quando existir), mensagem, origem,
parâmetros UTM, histórico de status, histórico de responsáveis, observações
internas e data/hora das ações. A primeira abertura SHALL registrar
`first_viewed_at`. Observações internas nunca SHALL ser exibidas ao visitante.

#### Scenario: Primeira visualização registrada

- **WHEN** um usuário autorizado abre uma consulta `new`
- **THEN** `first_viewed_at` é preenchido e o indicador de não lida é removido

#### Scenario: Produto excluído

- **WHEN** o produto da consulta foi excluído
- **THEN** a tela exibe o nome em snapshot sem link, sem erro

### Requirement: Gestão de status

O usuário autorizado SHALL alterar o status entre: `new`, `viewed`,
`in_service`, `waiting_customer`, `answered`, `completed`, `discarded`,
`spam`. Toda mudança SHALL registrar histórico (de/para, usuário, data) e
atualizar marcos (`first_answered_at` no primeiro atendimento, `completed_at`
na conclusão).

#### Scenario: Mudança para "em atendimento"

- **WHEN** o usuário altera o status para `in_service`
- **THEN** o histórico registra a transição e `first_answered_at` é preenchido
  na primeira vez

#### Scenario: Marcar como spam

- **WHEN** o usuário marca a consulta como `spam`
- **THEN** ela sai dos indicadores operacionais e o histórico registra a ação

### Requirement: Atribuição de responsável

Usuários com `catalog.inquiries.assign` SHALL atribuir ou alterar o
responsável pela consulta, com registro no histórico.

#### Scenario: Atribuição sem permissão

- **WHEN** um usuário sem `catalog.inquiries.assign` tenta atribuir responsável
- **THEN** a ação é negada no backend

### Requirement: Observações internas e ações rápidas

O usuário SHALL adicionar observações internas, copiar telefone/e-mail, abrir
conversa no WhatsApp (link com telefone do interessado) e registrar retorno
realizado manualmente.

#### Scenario: Observação interna registrada

- **WHEN** o usuário adiciona uma observação interna
- **THEN** ela é salva vinculada ao autor e exibida apenas no painel

### Requirement: Exportação CSV

Usuários com `catalog.inquiries.export` SHALL exportar as consultas filtradas
em CSV (UTF-8), sem depender de pacotes externos.

#### Scenario: Exportação respeitando filtros

- **WHEN** o usuário exporta com filtro de período ativo
- **THEN** o CSV contém apenas as consultas do período filtrado

#### Scenario: Exportação sem permissão

- **WHEN** um usuário sem `catalog.inquiries.export` tenta exportar
- **THEN** recebe 403

### Requirement: Indicadores no dashboard

O dashboard SHALL incluir: consultas novas, em atendimento, concluídas,
consultas por período, produtos mais consultados, categorias mais consultadas,
origem das consultas, taxa de conclusão e tempo médio até o primeiro
atendimento, usando agregações no banco (sem carregar registros em memória).

#### Scenario: Widgets com dados agregados

- **WHEN** o usuário abre o dashboard
- **THEN** os indicadores são calculados por consultas agregadas (`count`,
  `groupBy`, `avg`) e refletem o estado atual das consultas

