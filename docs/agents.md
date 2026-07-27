# Agentes de IA

Todos os agentes devem ler [`AGENTS.md`](../AGENTS.md). Ele é a regra comum para Claude Code, Codex, Cursor, OpenCode e TaskMaster.

- Claude Code: `CLAUDE.md` importa o fluxo do TaskMaster e este arquivo é a regra global do projeto.
- Codex: carrega `AGENTS.md` automaticamente a partir da raiz do repositório.
- Cursor: `.cursor/rules/ai-project.mdc` referencia estas regras.
- OpenCode: `AGENTS.md` é a instrução compartilhada e `opencode.json` mantém o MCP do TaskMaster.
- TaskMaster: `.taskmaster/config.json` usa respostas em Português; tarefas e documentação são versionadas.

Ao finalizar uma tarefa, informe arquivos alterados, validações executadas, riscos e pendências. Nunca exponha tokens de MCP ou chaves de provedores.
