# Proposal: Editor de texto rico (HugeRTE) no painel admin

## Intent

Permitir que administradores formatem os campos de conteúdo longo (`Post.content` e `Product.description/specifications/features`) sem escrever HTML manualmente.

## Scope

- Novo componente de formulário `App\Filament\Forms\Components\HtmlEditor`, baseado no HugeRTE (https://github.com/hugerte/hugerte), empacotado em `public/js/hugerte/hugerte-editor.js` (`npm run build:editor`).
- Aplicado a: `Post.content` e `Product.description/specifications/features`.
- O HTML gravado passa por `ContentSanitizer` antes de persistir.

Fora do escopo:

- Campos de texto simples renderizados como texto escapado no site (resumos, descrições curtas, bios, SEO).
- Upload de imagens pelo editor e tradução da interface do editor.

## Affected domains

- [x] `catalog-admin`
