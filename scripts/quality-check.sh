#!/usr/bin/env bash
set -euo pipefail

docker compose exec app composer validate --strict --no-check-publish
docker compose exec app vendor/bin/pint --test
docker compose exec app vendor/bin/phpstan analyse --memory-limit=1G
docker compose exec app php artisan test
docker compose exec app php artisan scramble:export --path=public/openapi.json

echo "Quality checks completed successfully."
