# OpenAPI

O Assinix usa Scramble para gerar documentação OpenAPI automaticamente a partir das rotas, Form Requests e Resources da aplicação.

## URLs

| Recurso | URL |
| --- | --- |
| UI interativa | `/docs/api` |
| OpenAPI JSON | `/docs/api.json` |
| Export local | `public/openapi.json` |

## Instalação

```bash
docker compose exec app composer require dedoc/scramble
docker compose exec app php artisan vendor:publish --provider="Dedoc\Scramble\ScrambleServiceProvider" --tag="scramble-config"
```

## Exportar specification

```bash
docker compose exec app php artisan scramble:export --path=public/openapi.json
```

## Autenticação

A API usa Bearer Token com Laravel Sanctum.

Header:

```http
Authorization: Bearer {TOKEN}
```

## Observação

A documentação fica liberada por padrão em ambiente local e testing. Para produção, o acesso deve ser revisado antes de liberar publicamente.
