#!/usr/bin/env bash
set -euo pipefail
project_root="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$project_root"
vendor/bin/pint
if [[ -x node_modules/.bin/prettier ]]; then node_modules/.bin/prettier --write 'resources/**/*.{js,jsx,ts,tsx,css,scss,json}'; fi
