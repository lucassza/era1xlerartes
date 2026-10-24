## ADDED Requirements

### Requirement: Editor de texto rico nos campos de conteúdo

Os campos de conteúdo longo renderizados como HTML no site (`Post.content` e `Product.description/specifications/features`) MUST usar o editor de texto rico do painel e MUST persistir o HTML sanitizado por `ContentSanitizer`, descartando scripts, atributos de evento e esquemas de link inseguros.

#### Scenario: Formulário exibe o editor

- **WHEN** um administrador abre o formulário de criação do recurso
- **THEN** o campo de conteúdo carrega o editor HugeRTE

#### Scenario: HTML inseguro é removido ao salvar

- **WHEN** o conteúdo enviado contém `<script>` ou `onclick`
- **THEN** o valor persistido não contém esses elementos nem atributos
