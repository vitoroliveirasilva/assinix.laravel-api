# Checks locais de qualidade

Antes de commitar, rode:

```bash
docker compose exec app composer validate --strict --no-check-publish
docker compose exec app vendor/bin/pint --test
docker compose exec app vendor/bin/phpstan analyse --memory-limit=1G
docker compose exec app php artisan test
docker compose exec app php artisan scramble:export --path=public/openapi.json
```

No Windows PowerShell, você pode usar:

```powershell
.\scripts\quality-check.ps1
```

No Linux/macOS:

```bash
chmod +x scripts/quality-check.sh
./scripts/quality-check.sh
```

## Quando usar `pint` sem `--test`

Use quando quiser corrigir automaticamente o estilo:

```bash
docker compose exec app vendor/bin/pint
```

Depois revise o diff antes de commitar:

```bash
git diff
```
