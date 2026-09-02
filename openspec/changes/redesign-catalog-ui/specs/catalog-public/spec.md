# catalog-public — deltas

## MODIFIED Requirements

### Requirement: Página inicial exibe o catálogo

A rota `/` SHALL exibir o catálogo de produtos contendo hero configurável
quando `hero_enabled=true` (campos `hero_title`, `hero_subtitle`,
`hero_cta_label`, `hero_cta_url`, `hero_image_path`, `hero_alignment`),
ou cabeçalho tradicional (título + intro) quando desativado,
seguido por categorias ativas, produtos em destaque, a explicação do fluxo de
consulta e últimos produtos. O campo de busca SHALL aparecer dentro do hero
quando ele estiver ativo e logo abaixo do cabeçalho quando estiver desativado.
A listagem de artigos do blog SHALL continuar fora da home.

#### Scenario: Visitante abre a home

- **WHEN** um visitante acessa `/`
- **THEN** vê o catálogo com hero (quando habilitado) ou título e intro,
  categorias, busca, destaques e últimos produtos paginados, sem nenhuma
  listagem de posts

#### Scenario: Catálogo sem produtos publicados

- **WHEN** não existem produtos publicados
- **THEN** a home exibe o estado vazio amigável e as categorias, sem erro técnico

#### Scenario: Estrutura básica da home permanece

- **WHEN** o visitante acessa `/`
- **THEN** vê (na ordem) hero com busca OU cabeçalho seguido de busca,
  categorias, destaques, "Como funciona a consulta" e últimos produtos, sem
  nenhuma listagem de posts

#### Scenario: Home explica o fluxo de consulta

- **WHEN** o visitante acessa `/`
- **THEN** vê uma seção com as três etapas da consulta (escolher, enviar,
  receber a resposta), deixando explícito que não há carrinho nem pagamento
  online

### Requirement: Card de produto

Cada card SHALL exibir foto principal, nome, descrição curta, categoria, selo
(destaque, lançamento ou indisponível, quando aplicável), botão "Consulte" e um
acesso ao detalhe do produto. O acesso ao detalhe MAY ser um botão de ícone,
desde que exponha nome acessível ("Ver detalhes de {produto}"); o nome do
produto SHALL continuar sendo link para o detalhe. O preço SHALL seguir o modo
configurado no produto: exibir valor, exibir "Sob consulta" ou ocultar.

#### Scenario: Preço em modo "Sob consulta"

- **WHEN** o produto está com `price_display_mode = on_request`
- **THEN** o card e o detalhe exibem o texto "Sob consulta" e nenhum valor

#### Scenario: Acesso ao detalhe sem texto visível

- **WHEN** o card renderiza o acesso ao detalhe como botão de ícone
- **THEN** o elemento expõe `aria-label` com "Ver detalhes de {produto}" e o
  nome do produto permanece clicável

### Requirement: Filtros e ordenação preservados na URL

Os filtros SHALL ser apresentados como facetas (opções de categoria,
disponibilidade e marca, faixa de preço e alternador de destaques) em um
formulário `GET`, disponível em trilho fixo no desktop e em folha inferior no
mobile, sem depender de JavaScript para aplicar o filtro. Os parâmetros de query
(`categoria`, `marca`, `disponibilidade`, `preco_min`, `preco_max`, `destaque`,
`ordenar`, `buscar`) SHALL permanecer inalterados, assim como a ordenação por
mais recentes, nome A–Z, nome Z–A, menor preço, maior preço, destaque e ordem
manual. Filtros e ordenação SHALL ser preservados na URL e na paginação.

#### Scenario: Filtro por categoria com paginação

- **WHEN** o visitante filtra por categoria e avança de página
- **THEN** o filtro permanece aplicado e refletido na URL

#### Scenario: Filtros no mobile

- **WHEN** o visitante abre `/produtos` em tela estreita
- **THEN** vê um botão "Filtrar" com a contagem de filtros ativos que abre a
  folha inferior com as mesmas facetas do desktop
