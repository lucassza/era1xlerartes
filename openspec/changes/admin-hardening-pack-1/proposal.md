# Proposal: Correções e segurança do painel admin (pacote 1)

## Intent

Corrigir defeitos no perfil do admin e reforçar a segurança de acesso ao painel.

## Scope

- Avatar no perfil do admin: salvar o perfil não apaga mais o avatar atual; enviar nova imagem substitui; toggle "Remover avatar atual" remove.
- Autenticação em dois fatores (2FA) opcional por aplicativo autenticador, com códigos de recuperação, configurada em `/admin/profile`.
- Alerta de alterações não salvas nos formulários do painel (`unsavedChangesAlerts`).

Fora do escopo:

- Tornar o 2FA obrigatório.
- 2FA por e-mail e área do site público.

## Approach

- `EditProfile` passa a usar `addMedia()` com o arquivo do Livewire e só limpa a coleção quando há novo arquivo ou pedido de remoção.
- `User` implementa `HasAppAuthentication`/`HasAppAuthenticationRecovery`; migration adiciona `app_authentication_secret` e `app_authentication_recovery_codes` (cast `encrypted`, ocultos na serialização).
- `AdminPanelProvider` habilita `multiFactorAuthentication([AppAuthentication::make()->recoverable()])` e `unsavedChangesAlerts()`.

## Affected domains

- [x] `catalog-admin`
