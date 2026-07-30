# Tasks: <Nome da mudança>

Tarefas em ordem de execução. Cada item DEVE ser pequeno o suficiente para ser validado isoladamente. Marque cada item com `[x]` quando concluído e referencie arquivos/códigos relevantes.

## 1. <Bloco 1>

- [ ] 1.1 <Tarefa pequena e verificável>
  - Arquivo: `app/...`
  - Validação: comando ou teste
- [ ] 1.2 <Tarefa>
  - Validação: comando ou teste

## 2. <Bloco 2>

- [ ] 2.1 <Tarefa>
- [ ] 2.2 <Tarefa>

## 3. Validação

- [ ] 3.1 Rodar `./scripts/test.sh` e garantir suite verde
- [ ] 3.2 Rodar `./scripts/lint.sh` e garantir zero erros
- [ ] 3.3 Validar manualmente a rota/comportamento X
- [ ] 3.4 Conferir logs e auditoria após execução

## 4. Documentação

- [ ] 4.1 Atualizar `docs/api.md` se rotas mudaram
- [ ] 4.2 Atualizar `docs/architecture.md` se arquitetura mudou
- [ ] 4.3 Atualizar `openspec/specs/<dominio>/spec.md` via `/opsx:sync` ou merge manual

## 5. Arquivamento

- [ ] 5.1 Rodar `/opsx:archive` para mover change e mesclar delta em `openspec/specs/`
