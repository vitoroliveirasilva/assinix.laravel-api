$ErrorActionPreference = "Stop"

docker compose exec app vendor/bin/phpstan analyse --generate-baseline --memory-limit=1G
docker compose exec app vendor/bin/phpstan analyse --memory-limit=1G

Write-Host "PHPStan baseline generated and validated successfully."
