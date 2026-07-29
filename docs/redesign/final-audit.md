# Auditoria final do redesign

**Estado da subtarefa:** em andamento. As validações automatizadas foram executadas e aprovadas. Esta auditoria não dispõe de navegador ou dispositivos reais; portanto, nenhum teste manual de navegador, axe, Lighthouse, leitor de tela, contraste renderizado, zoom ou dispositivo físico foi realizado.

## Escopo e fontes verificadas

- Contexto da sessão: `.tmp/tasks/tdb-mix-blog-redesign/task.json` e `subtask_13.json`.
- Descoberta de validação: `docs/redesign/automated-validation.md`.
- Código público atual: layouts, páginas, componentes públicos, `resources/css/app.css`, `resources/js/app.js`, `app/Services/SeoService.php`, testes de feature e artefatos em `public/build/`.

Os testes em `tests/Feature/SmokeTest.php` e `tests/Feature/Seo/SeoPagesTest.php` foram executados como parte da suíte completa aprovada.

## Acessibilidade

| Item | Resultado | Evidência / limitação |
| --- | --- | --- |
| Teclado | Não verificável | Há controles nativos, link de salto e navegações semânticas (`resources/views/components/layouts/app.blade.php`, `components/site/header.blade.php`), mas não houve percurso por teclado em navegador. |
| Foco visível | Aprovado (estático) | `resources/css/app.css` define `:focus-visible` com `outline: 3px solid var(--tdb-accent)` e offset; links de cards incluem estilos `focus-visible`. A aparência renderizada não foi inspecionada. |
| Restauração de foco em menu/diálogo | Aprovado (estático) | O menu móvel devolve foco ao gatilho ao fechar e o diálogo de busca guarda/restaura o elemento abridor (`components/site/header.blade.php` e `components/site/search-dialog.blade.php`). A interação em navegador não foi exercitada. |
| Contraste AA | Não verificável | Há tokens para temas claro/escuro em `resources/css/app.css`, porém contraste depende da renderização e dos estados; não houve medição de contraste. |
| Leitores de tela | Não verificável | O HTML contém `lang`, landmarks, rótulos, `aria-*`, textos alternativos e link de salto, mas nenhum leitor de tela foi usado. |
| Zoom de 200% | Não verificável | Não houve renderização nem inspeção com zoom. |
| Movimento reduzido | Aprovado (estático) | `resources/css/app.css` contém `@media (prefers-reduced-motion: reduce)` que reduz transições/animações e desativa rolagem suave. O comportamento no navegador não foi exercitado. |

## Responsividade

| Item | Resultado | Evidência / limitação |
| --- | --- | --- |
| Estrutura fluida e breakpoints | Aprovado (estático) | As páginas usam layouts fluidos (`max-w-*`, `minmax(0,1fr)`, grids e variantes `sm`, `md`, `lg`, `xl`); `html` tem largura mínima de `20rem`/320 px em `resources/css/app.css`. |
| Sem overflow em 320 px | Não verificável | Há `min-w-0` em áreas críticas e modal com largura limitada, mas não houve viewport de 320 px renderizado. |
| Sem overflow em tablet | Não verificável | Os breakpoints existem no código, mas nenhum viewport de tablet foi renderizado. |
| Sem overflow em notebook | Não verificável | Não houve viewport de notebook renderizado. |
| Sem overflow em desktop | Não verificável | Não houve viewport de desktop renderizado. |
| Sem overflow em ultrawide | Não verificável | Não houve viewport ultrawide renderizado. |

## SEO

| Item | Resultado | Evidência / limitação |
| --- | --- | --- |
| Título e description | Aprovado (estático) | O layout emite `<title>` e `meta[name=description]`; `SeoService` preenche ambos para home e post. |
| Canonical | Aprovado (estático) | O layout emite canonical quando fornecido e `SeoService` o preenche para home/post. |
| Robots | Aprovado (estático) | O layout emite `meta[name=robots]`; `SeoService` usa `index,follow` para home/post. `public/robots.txt` permite rastreamento. |
| JSON-LD | Aprovado (estático e automatizado) | O layout serializa `jsonLd`; `SeoService` gera `WebSite`/`Organization` e `BlogPosting`/breadcrumb quando os dados são válidos. A suíte aprovada inclui asserções de JSON válido em `SeoPagesTest.php`. |
| Resposta renderizada e indexação por crawler | Não verificável | Não houve servidor, requisição HTTP externa nem ferramenta de validação de dados estruturados. |

## Performance

| Item | Resultado | Evidência / limitação |
| --- | --- | --- |
| Fontes com `swap` | Aprovado (estático) | O artefato existente `public/build/assets/fonts-Co3yWjHJ.css` declara `font-display: swap` para Inter e Poppins; o manifest referencia esse asset. |
| Espaço reservado para imagens | Aprovado (estático) | Imagens públicas possuem `width`/`height`; capas também usam contêineres `aspect-*` em componentes editoriais e no artigo. |
| Lazy loading fora da dobra | Não verificável | Cards usam `loading="lazy"`; hero e capa do artigo usam `loading="eager" fetchpriority="high"`. A posição efetiva das imagens, e portanto a garantia de que todas as lazy estão fora da dobra, exige renderização. |
| Imagens responsivas | Aprovado (estático) | Capas usam `srcset` quando há imagens responsivas e `sizes` específicos por breakpoint nos componentes editoriais e na página de post. |
| Métricas de performance (LCP, CLS, INP, tamanho/transferência) | Não verificável | Lighthouse, DevTools e medições de rede não estavam disponíveis. A simples presença de `public/build/manifest.json` confirma artefatos existentes, não uma medição de performance. |

## Validações automatizadas requeridas

| Comando | Resultado nesta auditoria | Evidência |
| --- | --- | --- |
| `php artisan test` | Aprovado | 70 testes e 274 assertions aprovados no serviço Docker `app`. O PHPUnit emitiu apenas aviso ambiental ao gravar seu cache de resultados. |
| `vendor/bin/pint --test` | Aprovado | Pint verificou 156 arquivos sem problemas de estilo após a formatação limitada aos cinco arquivos apontados. |
| `vendor/bin/phpstan analyse` | Aprovado | PHPStan analisou 83 arquivos e não encontrou erros. |
| `npm run build -- --configLoader runner` | Aprovado | Build Vite de produção aprovado no container Node; o `configLoader runner` evita o cache temporário sem permissão em `node_modules`. |

## Bloqueios para encerrar a subtarefa

1. **Auditoria de navegador pendente** — reprodução: inspecionar teclado, foco, contraste AA, leitor de tela, zoom de 200%, `prefers-reduced-motion` e ausência de overflow nos viewports 320 px, tablet, notebook, desktop e ultrawide; critério afetado: checklist de acessibilidade e responsividade.
2. **Medição de performance pendente** — reprodução: executar auditoria de navegador (por exemplo, Lighthouse) com registro dos resultados; critério afetado: métricas de performance e confirmação visual do carregamento de imagens.

Nenhuma falha de código foi declarada nesta auditoria: os itens pendentes estão marcados como **não verificáveis**, e não como aprovados, até que existam evidências de execução.
