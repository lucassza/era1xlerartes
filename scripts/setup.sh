#!/usr/bin/env bash
set -euo pipefail
project_root="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$project_root"
composer install
if [[ ! -f .env ]]; then cp .env.example .env; fi
php artisan key:generate --force
npm install --ignore-scripts
npm run build
echo "Setup concluído. Execute ./scripts/dev.sh para iniciar o ambiente."
