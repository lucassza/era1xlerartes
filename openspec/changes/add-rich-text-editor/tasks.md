## 1. Implementação

- [x] 1.1 Criar `HtmlEditor` e a view Blade `filament/forms/components/html-editor`
- [x] 1.2 Empacotar o HugeRTE com esbuild (`npm run build:editor`)
- [x] 1.3 Aplicar o editor aos campos: `Post.content` e `Product.description/specifications/features`
- [x] 1.4 Sanitizar `description`, `specifications` e `features` no `Product::saving`

## 2. Verificação

- [ ] 2.1 Rodar `HtmlEditorTest` e a suíte do painel em ambiente com PHP/Docker
- [ ] 2.2 Validar manualmente o editor no navegador (criar/editar e salvar)
