# catalog-admin Specification

## Purpose
TBD - created by archiving change add-product-catalog. Update Purpose after archive.
## Requirements
### Requirement: Cadastro completo de produtos

O painel SHALL ter resource de produtos no grupo de navegação "Catálogo"
permitindo cadastrar: nome, slug (automático editável), código interno, SKU,
categoria, subcategoria, marca, descrições curta e completa, especificações,
características, preço, preço promocional (informativo), modo de exibição do
preço, unidade, disponibilidade, quantidade informativa, imagem principal,
galeria, anexos, tags, atributos, produtos relacionados, texto do botão de
consulta, SEO (título, descrição, imagem de compartilhamento), status, data de
publicação, destaque e ordem manual.

#### Scenario: Cadastro de produto completo

- **WHEN** um usuário com `catalog.products.create` preenche o formulário completo
- **THEN** o produto é salvo com todos os dados e aparece conforme seu status

#### Scenario: Slug gerado automaticamente

- **WHEN** o administrador informa o nome sem preencher o slug
- **THEN** o slug é gerado automaticamente de forma única

### Requirement: Status do produto

O produto SHALL ter status `draft`, `published`, `unavailable` e `archived`.
Somente `published` (e `unavailable` quando habilitado nas settings) SHALL
aparecer no catálogo público. A publicação SHALL exigir a permissão
`catalog.products.publish`.

#### Scenario: Produto indisponível visível com selo

- **WHEN** o produto está `unavailable` e a setting `show_unavailable` está ativa
- **THEN** ele aparece no catálogo com selo claro de indisponibilidade

#### Scenario: Usuário sem permissão de publicar

- **WHEN** um usuário sem `catalog.products.publish` tenta publicar
- **THEN** a ação é negada no backend (não apenas oculta na interface)

### Requirement: Gestão de imagens e anexos

O cadastro SHALL permitir upload múltiplo de imagens (Media Library/MinIO),
definição da imagem principal, ordenação da galeria, exclusão, texto
alternativo e legenda, com validação de formato e tamanho, geração de
miniaturas/conversões e remoção segura dos arquivos. Anexos SHALL aceitar
documentos (PDF, manual, ficha técnica, certificado). URLs privadas ou
credenciais do storage nunca SHALL ser expostas.

#### Scenario: Upload de galeria ordenada

- **WHEN** o administrador envia várias imagens e reordena a galeria
- **THEN** a ordem é persistida e refletida na página pública do produto

#### Scenario: Arquivo com extensão perigosa

- **WHEN** é enviado arquivo com extensão bloqueada (ex.: `.php`, `.exe`)
- **THEN** o upload é rejeitado com mensagem amigável

### Requirement: Categorias hierárquicas de catálogo

O painel SHALL gerenciar categorias com nome, slug, descrição, imagem, ícone
opcional, categoria pai, ordem, status ativo e SEO, suportando subcategorias.
Ciclos na hierarquia SHALL ser impedidos.

#### Scenario: Tentativa de ciclo

- **WHEN** o administrador tenta definir como pai uma categoria descendente da atual
- **THEN** a validação rejeita a operação

#### Scenario: Reordenação de categorias

- **WHEN** o administrador altera a ordem de exibição
- **THEN** o menu e as listagens públicas refletem a nova ordem

### Requirement: Gestão de marcas

O painel SHALL gerenciar marcas com nome, slug, descrição, logotipo, site
opcional, status ativo e ordem, protegido por `catalog.brands.manage`.

#### Scenario: Cadastro de marca

- **WHEN** um usuário autorizado cadastra uma marca com logotipo
- **THEN** a marca fica disponível para associação a produtos e filtros públicos

### Requirement: Atributos informativos

O painel SHALL gerenciar grupos de atributos e atributos (cor, tamanho,
material, peso, dimensões, capacidade, voltagem, modelo, fabricante etc.),
associá-los a categorias e atribuir valores a produtos. Atributos SHALL ser
apenas informativos — sem variações compráveis.

#### Scenario: Grupo associado a categoria

- **WHEN** um grupo de atributos é associado a uma categoria
- **THEN** produtos dessa categoria podem receber valores desses atributos

### Requirement: Configurações do catálogo

O painel SHALL ter página de configurações (gate `catalog.settings.manage`)
para: título, descrição da home, banner, itens por página, ordenação padrão,
exibição de preços, exibição de indisponíveis, e-mails de notificação,
mensagem de confirmação, texto padrão do botão "Consulte", telefone/WhatsApp
comercial, flag de CAPTCHA, destaques e SEO da home.

#### Scenario: Alteração de configuração

- **WHEN** um usuário com `catalog.settings.manage` altera os itens por página
- **THEN** o valor é persistido no singleton e aplicado às listagens públicas,
  com cache invalidado

#### Scenario: Acesso sem permissão às settings

- **WHEN** um usuário sem `catalog.settings.manage` acessa a página de configurações
- **THEN** recebe 403

