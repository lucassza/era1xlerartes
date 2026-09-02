# Design: Redesign visual do catálogo

## Contexto

O redesign foi desenhado em um canvas de artboards antes de virar código
(`docs/redesign/canvas/`, publicado como Artifact). Oito telas: home, listagem,
folha de filtros e detalhe de produto, em desktop e mobile, mais uma folha com a
base visual.

## Decisões

### Nenhum token novo

Todas as cores, raios, sombras e a dupla tipográfica já existiam em
`resources/css/app.css` (`--era1x-*` mapeadas em `@theme inline`). Os tons de
apoio do canvas (`#f7edee`, `#fdf1f4`) são expressos como `bg-primary/5` e
`bg-secondary/10`, evitando cores soltas.

### Filtros: facetas em vez de `<select>`, com o mesmo contrato

Trocar `<select>` por rádios e pílulas muda só a apresentação: cada faceta
continua enviando o mesmo campo (`categoria`, `marca`, `disponibilidade`) com o
mesmo valor, dentro de um `<form method="GET">` submetido pelo botão "Aplicar
filtros". Nada depende de JavaScript para filtrar.

O formulário virou `catalog/_filters.blade.php`, incluído duas vezes — no trilho
do desktop e na folha do mobile. `$scope` prefixa os `id` dos campos para as
duas instâncias não colidirem. A duplicação do markup é deliberada: a
alternativa (um só bloco alternando entre `position: fixed` e estático) exigiria
sobrescrever o `display` inline que o `x-show` do Alpine aplica.

### Facetas sem contagem

O canvas mostrava a contagem de produtos por faceta. `PublicProductController`
não fornece esse dado e obtê-lo exigiria consultas de agregação por faceta — fora
do escopo de um redesign visual. As facetas foram implementadas sem contagem em
vez de exibir número inventado.

### Busca dentro do hero

Com o hero ativo, o campo de busca vive dentro dele; com o hero desativado, a
home mantém o cabeçalho tradicional e a busca solta logo abaixo. Isso preserva o
comportamento configurável do hero (`hero_enabled`) sem deixar a home sem busca.

### Card: "Ver detalhes" como botão de ícone

O link textual "Ver detalhes" virou um botão de ícone ao lado do CTA de
consulta, com `aria-label="Ver detalhes de {produto}"`. O nome acessível
permanece; o texto visível some em favor do peso do CTA principal. O card também
continua com o nome do produto como link para o detalhe.

### Imagens de demonstração são ilustrações

As capas anexadas pelo seeder são ilustrações vetoriais planas geradas por
`database/seeders/assets/catalog/generate_product_images.py`, não fotos. Elas
existem para o catálogo de demonstração não ficar sem imagem; qualquer capa já
existente (inclusive foto real enviada pelo painel) é preservada.

## Alternativas descartadas

- **Filtrar por link em vez de formulário**: cada faceta como `<a>` que reescreve
  a query. Elimina o botão "Aplicar", mas dispara uma requisição por clique e
  complica combinar faixa de preço com as demais facetas.
- **Reescrever `<x-ui.section-heading>`**: o componente é compartilhado com o
  blog; mudá-lo afetaria telas fora do escopo. As seções da home passaram a
  omitir o `label` para não usar a barra lateral do componente.
