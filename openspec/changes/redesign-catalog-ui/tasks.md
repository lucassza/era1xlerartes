# Tarefas: Redesign visual do catálogo

## 1. Base

- [x] 1.1 Desenhar as oito telas no canvas (`docs/redesign/canvas/`) e validar a
      paleta, o logotipo e a dupla tipográfica contra `resources/css/app.css`
- [x] 1.2 Confirmar que o redesign não exige token novo em `app.css`

## 2. Componentes compartilhados

- [x] 2.1 `catalog/_product-card.blade.php`: imagem maior, preço em display, CTA
      de consulta em largura total e acesso ao detalhe como botão de ícone
- [x] 2.2 `components/site/header.blade.php`: navegação em pílulas e busca
      arredondada, preservando o menu mobile Alpine e o `<x-site.auth-menu>`
- [x] 2.3 `components/site/footer.blade.php`: rodapé em vinho com marca,
      institucional, catálogo e atendimento, mantendo telefone, WhatsApp e e-mails

## 3. Home

- [x] 3.1 `components/site/catalog-hero.blade.php`: hero sólido com motivo
      geométrico, busca e CTAs, mantendo os campos configuráveis do admin
- [x] 3.2 `catalog/home.blade.php`: categorias com ícone e descrição, destaques,
      seção "Como funciona a consulta" e novidades

## 4. Listagem

- [x] 4.1 Extrair `catalog/_filters.blade.php` com as facetas (rádios e pílulas)
      preservando os parâmetros de query
- [x] 4.2 `catalog/index.blade.php`: trilho fixo no desktop, folha inferior no
      mobile, barra de resultados com chips e ordenação

## 5. Detalhe do produto

- [x] 5.1 `catalog/show.blade.php`: galeria com miniaturas reativas, bloco de
      preço e consulta, atributos em grade e barra de ação fixa no mobile

## 6. Dados de demonstração

- [x] 6.1 Gerar as 12 ilustrações e o script que as produz
- [x] 6.2 `CatalogDemoSeeder`: anexar a capa de cada produto de forma idempotente

## 7. Verificação

- [x] 7.1 `npm run build` (Tailwind gera as utilidades novas)
- [x] 7.2 Compilar as views alteradas e conferir a sintaxe do PHP resultante
- [x] 7.3 `php artisan test` — 189 passando; a única falha
      (`BlogContentSeederTest`, contagem de tags) é anterior a esta mudança e não
      toca o catálogo
- [x] 7.4 Rodar `CatalogDemoSeeder` no banco de teste: 12 de 12 produtos com capa
- [ ] 7.5 Revisão visual no navegador em desktop e mobile
