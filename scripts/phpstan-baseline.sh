#!/usr/bin/env bash
set -euo pipefail

docker compose exec app vendor/bin/phpstan analyse --generate-baseline --memory-limit=1G
docker compose exec app vendor/bin/phpstan analyse --memory-limit=1G

echo "PHPStan baseline generated and validated successfully."
