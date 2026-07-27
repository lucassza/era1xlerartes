# Agentes de IA

Todos os agentes devem ler [`AGENTS.md`](../AGENTS.md). Ele é a regra comum para Claude Code, Codex, Cursor, OpenCode e TaskMaster.

- Claude Code: `CLAUDE.md` importa o fluxo do TaskMaster e este arquivo é a regra global do projeto.
- Codex: carrega `AGENTS.md` automaticamente a partir da raiz do repositório.
- Cursor: `.cursor/rules/ai-project.mdc` referencia estas regras.
- OpenCode: `AGENTS.md` é a instrução compartilhada e `opencode.json` mantém o MCP do TaskMaster.
- TaskMaster: `.taskmaster/config.json` usa respostas em Português; tarefas e documentação são versionadas.

## OpenSpec

O projeto adota OpenSpec (1.6+) para desenvolvimento orientado a especificações. A estrutura vive em `openspec/`:

- `openspec/config.yaml`: contexto do projeto e regras por artefato exibidos aos agentes.
- `openspec/specs/`: capacidades vigentes do sistema (fonte de verdade pós-arquivamento).
- `openspec/changes/<nome>/`: mudanças em andamento, com `proposal.md`, `design.md`, `specs/` e `tasks.md`.

Fluxo padrão para mudanças não triviais:

1. Propor a mudança (`/opsx:propose` ou criação manual da pasta da change).
2. Revisar proposal, design e specs antes de codar.
3. Implementar seguindo `tasks.md` (`/opsx:apply`).
4. Validar com `openspec validate <change> --strict` e arquivar ao concluir (`/opsx:archive`), o que promove os requisitos para `openspec/specs/`.

Os slash commands `/opsx:*` estão disponíveis nas ferramentas configuradas (Claude Code, Cursor, OpenCode e demais integrações geradas pelo `openspec init --tools all`). Comandos CLI úteis: `openspec list`, `openspec status --change <nome>`, `openspec doctor`.

Ao finalizar uma tarefa, informe arquivos alterados, validações executadas, riscos e pendências. Nunca exponha tokens de MCP ou chaves de provedores.
