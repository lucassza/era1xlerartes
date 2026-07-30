# Agentes de IA

Todos os agentes devem ler [`AGENTS.md`](../AGENTS.md). Ele é a regra comum para Claude Code, Codex, Cursor e OpenCode.

- Claude Code: `CLAUDE.md` + gate OpenSpec (`/opsx:*`).
- Codex: carrega `AGENTS.md` automaticamente a partir da raiz do repositório.
- Cursor: `.cursor/rules/` (inclui `openspec-default.mdc`) e commands `/opsx-*`.
- OpenCode: `AGENTS.md` + commands/skills OpenSpec em `.opencode/`.

## OpenSpec

O projeto adota OpenSpec para desenvolvimento orientado a especificações. A estrutura vive em `openspec/`:

- `openspec/config.yaml`: contexto do projeto e regras por artefato.
- `openspec/specs/`: capacidades vigentes do sistema.
- `openspec/changes/<nome>/`: mudanças em andamento (`proposal.md`, `design.md`, `specs/`, `tasks.md`).

Fluxo padrão para mudanças não triviais:

1. Propor a mudança (`/opsx:propose` ou `/opsx-propose`).
2. Revisar proposal, design e specs antes de codar.
3. Implementar seguindo `tasks.md` (`/opsx:apply`).
4. Validar e arquivar (`/opsx:archive`).

Ao finalizar uma tarefa, informe arquivos alterados, validações executadas, riscos e pendências. Nunca exponha tokens ou chaves de provedores.
