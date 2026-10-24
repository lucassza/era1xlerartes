## ADDED Requirements

### Requirement: Avatar preservado ao salvar o perfil

Salvar o perfil do admin sem enviar nova imagem MUST manter o avatar atual. Enviar nova imagem MUST substituir o avatar, e o administrador MUST poder removê-lo explicitamente.

#### Scenario: Salvar sem nova imagem

- **WHEN** o admin com avatar altera apenas o nome e salva
- **THEN** o avatar continua o mesmo

#### Scenario: Remover avatar

- **WHEN** o admin marca "Remover avatar atual" e salva
- **THEN** a coleção `avatar` fica vazia

### Requirement: Autenticação em dois fatores opcional

O painel MUST oferecer 2FA por aplicativo autenticador com códigos de recuperação, configurável no perfil. O segredo e os códigos MUST ser persistidos criptografados e MUST NOT aparecer na serialização do usuário.

#### Scenario: Segredo criptografado

- **WHEN** o segredo 2FA é salvo
- **THEN** o valor no banco difere do segredo em texto puro e o usuário o recupera descriptografado

### Requirement: Alerta de alterações não salvas

Os formulários do painel MUST avisar o usuário ao tentar sair com alterações não salvas.

#### Scenario: Recurso habilitado

- **WHEN** o painel é carregado
- **THEN** `unsavedChangesAlerts` está habilitado
