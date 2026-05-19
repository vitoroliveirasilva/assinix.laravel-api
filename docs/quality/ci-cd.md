# CI/CD

O Assinix usa GitHub Actions para validar qualidade automaticamente em `push` e `pull_request`.

## Workflows

| Arquivo | Objetivo |
| --- | --- |
| `.github/workflows/ci.yml` | Composer validate, migrations, Pint, Larastan, testes e export OpenAPI |
| `.github/workflows/docker-build.yml` | Validação do docker compose e build da imagem PHP |
| `.github/dependabot.yml` | Atualizações semanais de dependências Composer e GitHub Actions |

## Pipeline principal

O workflow `CI` executa:

1. checkout do repositório;
2. setup do PHP 8.4;
3. validação do `composer.json`;
4. instalação das dependências;
5. preparação do ambiente Laravel;
6. migrations em PostgreSQL;
7. Laravel Pint em modo `--test`;
8. Larastan/PHPStan;
9. testes automatizados;
10. exportação do OpenAPI.

## Por que rodar migrations no CI?

Para validar que o schema relacional continua íntegro em PostgreSQL, que é o banco principal da aplicação.

## Por que os testes usam `.env.testing`?

Para rodar os testes de forma rápida e isolada. A migration em PostgreSQL já cobre o smoke test do banco principal, enquanto a suíte de testes pode usar SQLite em memória.
