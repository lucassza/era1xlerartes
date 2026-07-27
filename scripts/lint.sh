#!/usr/bin/env bash
set -euo pipefail
project_root="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$project_root"
composer lint
composer analyse
if [[ -x node_modules/.bin/eslint ]]; then node_modules/.bin/eslint resources/js; fi
if [[ -x node_modules/.bin/stylelint ]]; then node_modules/.bin/stylelint 'resources/**/*.{css,scss}'; fi
