# Qualidade e CI/CD

O Assinix usa uma esteira de qualidade para validar o projeto automaticamente.

## Ferramentas

| Ferramenta | Função |
| --- | --- |
| Pest | Testes automatizados |
| Laravel Pint | Padronização de estilo |
| Larastan/PHPStan | Análise estática |
| GitHub Actions | Pipeline de CI |
| Docker Compose | Validação de build local/CI |

## Comandos locais

Rodar testes:

```bash
docker compose exec app php artisan test