# Proposta: Redesign visual do catálogo (home, listagem e produto)

## Why

O catálogo funciona, mas o visual não acompanha: a home coloca a busca solta
entre o hero e as categorias, a listagem usa `<select>` para todos os filtros
(inclusive no mobile, dentro de um `<details>` apertado), o card de produto dá
o mesmo peso ao preço e ao link secundário, e o rodapé é um bloco claro sem
presença de marca.

Nada disso é bug — é hierarquia. O redesign foi desenhado antes em um canvas
(`docs/redesign/canvas/`) e aprovado: mesma paleta, mesmo logotipo, mesmas
fontes, nenhum token novo em `resources/css/app.css`.

## What Changes

- **Home**: a busca passa para dentro do hero (junto do CTA configurado e de um
  link para o contato); entra uma seção "Como funciona a consulta" com as três
  etapas do fluxo de interesse, já que o catálogo não tem carrinho nem checkout;
  categorias ganham ícone, descrição e contagem de subcategorias; destaques e
  novidades passam a exibir quatro produtos cada.
- **Listagem**: os filtros saem dos `<select>` e viram facetas (rádios e pílulas)
  em um formulário reaproveitado por `catalog/_filters.blade.php`, usado no
  trilho fixo do desktop e dentro de uma folha inferior (bottom sheet) no mobile,
  aberta por um botão "Filtrar" com a contagem de filtros ativos. Os parâmetros
  de query continuam os mesmos (`categoria`, `marca`, `disponibilidade`,
  `preco_min`, `preco_max`, `destaque`, `ordenar`, `buscar`).
- **Card de produto**: bloco de imagem maior, preço em tipografia de display, CTA
  de consulta ocupando a largura e o acesso ao detalhe como botão de ícone com
  nome acessível ("Ver detalhes de {produto}").
- **Detalhe do produto**: galeria com miniaturas reativas, bloco de preço e
  consulta destacado, atributos em grade e barra de ação fixa no mobile.
- **Header e rodapé**: navegação em pílulas, busca arredondada e rodapé em vinho
  com as quatro colunas de marca, institucional, catálogo e atendimento.
- **Imagens de demonstração**: `CatalogDemoSeeder` passa a anexar uma ilustração
  por produto na coleção `cover` (idempotente, preserva capa existente).

## Impact

- Sem mudança de rotas, contratos de query, regras de negócio ou ACL.
- Sem migration e sem alteração de controller: as facetas usam os dados que
  `PublicProductController` já entrega (categorias, marcas, faixa de preço).
- Não há contagem por faceta — o controller não fornece esse dado hoje e não foi
  incluído nesta mudança.
- Views afetadas: `catalog/home`, `catalog/index`, `catalog/show`,
  `catalog/_product-card`, novo `catalog/_filters`, `components/site/header`,
  `components/site/footer`, `components/site/catalog-hero`.
