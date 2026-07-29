# product-inquiry Specification

## Purpose
TBD - created by archiving change add-product-catalog. Update Purpose after archive.
## Requirements
### Requirement: Botão "Consulte" e formulário

O botão "Consulte" SHALL estar presente no card do produto, na página de
detalhes e visível no mobile. Ao clicar, SHALL abrir modal (ou página) com
formulário contendo: produto preenchido automaticamente, nome, e-mail,
telefone/WhatsApp, empresa (opcional), cidade (opcional), estado (opcional),
assunto, mensagem, consentimento de tratamento de dados e campo honeypot
oculto. O visitante SHALL conseguir enviar sem autenticação. O texto do botão
pode ser personalizado por produto e ter padrão nas settings.

#### Scenario: Abertura do modal no detalhe

- **WHEN** o visitante clica em "Consulte" na página do produto
- **THEN** o modal abre com o produto já identificado, sem exigir login

#### Scenario: Texto personalizado do botão

- **WHEN** o produto define `inquiry_button_text`
- **THEN** card e detalhe usam esse texto em vez do padrão das settings

### Requirement: Registro da consulta antes de notificar

Toda consulta SHALL ser persistida em transação antes de qualquer tentativa de
notificação, salvando: produto (referência nullable), snapshot do nome do
produto, dados do interessado, assunto, mensagem, origem, URL de origem,
parâmetros UTM, IP (quando permitido), user agent, status inicial `new` e
registro inicial no histórico de status. O banco SHALL ser a fonte principal —
falha de e-mail nunca perde a consulta.

#### Scenario: Envio com sucesso

- **WHEN** o visitante envia o formulário válido
- **THEN** a consulta é salva com status `new`, o histórico inicial é criado e
  o visitante vê confirmação amigável de registro

#### Scenario: Falha no envio de e-mail

- **WHEN** a notificação por e-mail falha após o salvamento
- **THEN** a consulta permanece registrada e a falha é logada sem expor erro ao visitante

### Requirement: Snapshot do produto

O nome do produto SHALL ser copiado para a consulta no momento do envio e
preservado mesmo que o produto seja alterado ou excluído depois.

#### Scenario: Produto excluído após consulta

- **WHEN** um produto consultado é excluído posteriormente
- **THEN** a consulta permanece com `product_name` e `product_id` nulo, sem quebra

### Requirement: Validação e proteção anti-spam

O formulário SHALL validar campos obrigatórios e formatos, limitar o tamanho
da mensagem, sanitizar entradas, aplicar honeypot (`website` proibido),
proteção CSRF e rate limit de 5 envios por 10 minutos por IP. Tentativas
suspeitas SHALL ser registradas. CAPTCHA SHALL ser uma flag configurável nas
settings (sem integração obrigatória nesta entrega).

#### Scenario: Honeypot preenchido

- **WHEN** o campo oculto `website` vem preenchido
- **THEN** a requisição falha na validação anti-spam e nada é salvo

#### Scenario: Rate limit excedido

- **WHEN** o mesmo IP envia mais de 5 consultas em 10 minutos
- **THEN** recebe resposta 429 e a consulta excedente não é salva

#### Scenario: Mensagem acima do limite

- **WHEN** a mensagem ultrapassa o tamanho máximo configurado
- **THEN** a validação rejeita com mensagem amigável e nada é salvo

### Requirement: Consentimento LGPD

O formulário SHALL exibir texto sobre a finalidade da coleta, exigir
consentimento explícito e linkar a política de privacidade. Somente os dados
necessários ao atendimento SHALL ser armazenados.

#### Scenario: Envio sem consentimento

- **WHEN** o visitante envia sem aceitar o tratamento de dados
- **THEN** a validação rejeita e a consulta não é registrada

### Requirement: Notificações assíncronas

Após salvar, o sistema SHALL disparar evento de consulta recebida; listeners
SHALL enfileirar notificação aos e-mails administrativos configurados e,
opcionalmente, confirmação automática ao visitante (conforme settings), sem
bloquear a resposta do formulário.

#### Scenario: Notificação enfileirada

- **WHEN** uma consulta é registrada
- **THEN** jobs de e-mail são despachados para a fila e a resposta ao
  visitante não aguarda o envio

#### Scenario: Sem e-mails configurados

- **WHEN** não há e-mails de notificação nas settings
- **THEN** a consulta é salva normalmente e nenhum envio é tentado

