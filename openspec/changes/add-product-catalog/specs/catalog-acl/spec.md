# Spec: ACL do catálogo

## ADDED Requirements

### Requirement: Permissões granulares

O sistema SHALL criar as permissões: `catalog.products.view`,
`catalog.products.create`, `catalog.products.update`,
`catalog.products.delete`, `catalog.products.publish`,
`catalog.categories.manage`, `catalog.brands.manage`,
`catalog.attributes.manage`, `catalog.inquiries.view`,
`catalog.inquiries.update`, `catalog.inquiries.assign`,
`catalog.inquiries.export` e `catalog.settings.manage`, semeadas de forma
idempotente em `RolesAndPermissionsSeeder`.

#### Scenario: Seeder idempotente

- **WHEN** `RolesAndPermissionsSeeder` é executado mais de uma vez
- **THEN** as permissões `catalog.*` existem exatamente uma vez

#### Scenario: Atribuição às roles existentes

- **WHEN** o seeder roda
- **THEN** Super Admin recebe todas; Editor recebe gestão de produtos e
  consultas (exceto `catalog.settings.manage`); Author não recebe nenhuma

### Requirement: Autorização no backend

As permissões SHALL ser aplicadas em policies (`ProductPolicy`,
`CatalogCategoryPolicy`, `BrandPolicy`, `ProductInquiryPolicy`) registradas em
`AuthServiceProvider`, e verificadas nas rotas, controllers, services, actions
e resources do painel. Ocultação de interface SHALL ser apenas consequência,
nunca o mecanismo de proteção.

#### Scenario: Acesso direto negado

- **WHEN** um usuário autenticado sem `catalog.products.update` chama a ação de
  edição de produto diretamente
- **THEN** recebe 403 mesmo com a interface oculta

#### Scenario: Super Admin irrestrito

- **WHEN** um Super Admin executa qualquer ação do catálogo
- **THEN** o `Gate::before` existente permite a ação

### Requirement: Menus e botões condicionais

Os itens de navegação "Catálogo" e "Consultas de produtos", bem como botões de
criar, publicar, exportar e atribuir, SHALL ser exibidos apenas quando o
usuário possuir a permissão correspondente.

#### Scenario: Usuário sem acesso ao catálogo

- **WHEN** um Author acessa o painel
- **THEN** não vê os menus de catálogo e consultas, e acessos diretos retornam 403

### Requirement: Rotas públicas sem autenticação

As rotas públicas do catálogo e o envio de consulta SHALL permanecer
acessíveis a visitantes anônimos, sem qualquer verificação de permissão.

#### Scenario: Visitante anônimo navega e consulta

- **WHEN** um visitante não autenticado acessa `/`, `/produtos`,
  `/produtos/{slug}` e envia uma consulta válida
- **THEN** todas as ações funcionam sem redirecionamento para login
