# Assinix Laravel API

API REST profissional em PHP/Laravel para gerenciamento de assinaturas, vencimentos e gastos recorrentes.

O Assinix permite que usuários cadastrem serviços recorrentes como streaming, internet, faculdade, servidores, ferramentas SaaS, academia, cursos, hospedagem, domínios e outros gastos semanais, mensais, anuais ou personalizados.

## Objetivo

Este projeto foi desenvolvido como portfólio back-end com foco em arquitetura limpa, Laravel, banco relacional, autenticação, autorização, testes automatizados, Docker, documentação OpenAPI, logs, auditoria, integração externa e CI/CD.

## Stack

- PHP 8.4
- Laravel 13
- PostgreSQL
- Redis
- Nginx
- Mailpit
- Docker
- Laravel Sanctum
- Pest
- Scramble OpenAPI
- AwesomeAPI
- GitHub Actions
- Laravel Pint
- PHPStan/Larastan

## Funcionalidades

- Autenticação com Sanctum
- Cadastro, login, logout e logout-all
- Perfil do usuário
- Usuário ativo/inativo
- CRUD de categorias
- CRUD de formas de pagamento
- CRUD de assinaturas
- Status de assinatura: active, paused, canceled, expired
- Recorrência: weekly, monthly, yearly, custom
- Próximos vencimentos
- Assinaturas vencidas
- Dashboard financeiro
- Conversão de moedas
- Cache de cotações
- Fallback para última cotação conhecida
- Histórico de assinaturas
- Auditoria
- Logs estruturados com request_id
- Health check completo
- Documentação OpenAPI

## Arquitetura

Fluxo principal da API:

```txt
Request HTTP
  ↓
Middleware
  ↓
Form Request
  ↓
Controller
  ↓
Action
  ↓
Service
  ↓
Model / Eloquent
  ↓
Resource
  ↓
ApiResponse
```

## Estrutura principal

```txt
app/
  Actions/
  Enums/
  Http/
    Controllers/
    Middleware/
    Requests/
    Resources/
  Models/
  Policies/
  Services/
  Support/

database/
  factories/
  migrations/
  seeders/

docs/
  adr/
  api/
  learning/

tests/
  Feature/
  Unit/
```

## Subindo o ambiente

Copie o arquivo de ambiente:

```bash
cp .env.example .env
```

No PowerShell:

```powershell
Copy-Item .env.example .env
```

Suba os containers:

```bash
docker compose up -d --build
```

Instale as dependências:

```bash
docker compose exec app composer install
```

Gere a chave da aplicação:

```bash
docker compose exec app php artisan key:generate
```

Rode as migrations:

```bash
docker compose exec app php artisan migrate
```

Opcionalmente, rode seeders:

```bash
docker compose exec app php artisan db:seed
```

## Serviços locais

| Serviço | URL |
| --- | --- |
| API | http://localhost:8080 |
| Health | http://localhost:8080/health |
| API Health | http://localhost:8080/api/v1/health |
| Docs OpenAPI | http://localhost:8080/docs/api |
| OpenAPI JSON | http://localhost:8080/docs/api.json |
| Mailpit | http://localhost:8025 |

## Testes

Rodar todos os testes:

```bash
docker compose exec app php artisan test
```

Rodar testes específicos:

```bash
docker compose exec app php artisan test --filter=AuthTest
docker compose exec app php artisan test --filter=CategoryCrudTest
docker compose exec app php artisan test --filter=SubscriptionCrudTest
docker compose exec app php artisan test --filter=DashboardTest
docker compose exec app php artisan test --filter=CurrencyRateTest
docker compose exec app php artisan test --filter=AuditLogTest
```

## Qualidade de código

Rodar todos os testes:

```bash
docker compose exec app composer test
```

Validar estilo com Laravel Pint:

```bash
docker compose exec app composer pint:test
```

Corrigir estilo automaticamente:

```bash
docker compose exec app composer pint
```

Rodar análise estática com Larastan:

```bash
docker compose exec app composer analyse
```

Rodar a esteira local completa:

```bash
docker compose exec app composer quality
```

Exportar OpenAPI:

```bash
docker compose exec app composer openapi:export
```

## CI/CD

O projeto possui uma esteira de qualidade planejada para GitHub Actions com:

- instalação de dependências via Composer;
- preparação do Laravel;
- migrations em PostgreSQL;
- execução dos testes;
- validação de estilo com Laravel Pint;
- análise estática com Larastan/PHPStan;
- exportação da documentação OpenAPI;
- validação de build Docker.

O objetivo é garantir que o projeto continue saudável a cada push ou pull request.

## Documentação OpenAPI

Instalar Scramble:

```bash
docker compose exec app composer require dedoc/scramble
```

Publicar configuração:

```bash
docker compose exec app php artisan vendor:publish --provider="Dedoc\Scramble\ScrambleServiceProvider" --tag="scramble-config"
```

Acessar documentação:

```txt
http://localhost:8080/docs/api
```

Exportar specification:

```bash
docker compose exec app php artisan scramble:export --path=public/openapi.json
```

## Padrão de resposta

Sucesso:

```json
{
  "success": true,
  "message": "Request completed successfully.",
  "data": {},
  "errors": null,
  "meta": {
    "request_id": "..."
  }
}
```

Erro:

```json
{
  "success": false,
  "message": "Os dados informados são inválidos.",
  "data": null,
  "errors": {},
  "meta": {
    "request_id": "..."
  }
}
```

Resposta paginada:

```json
{
  "success": true,
  "message": "Resources returned successfully.",
  "data": [],
  "errors": null,
  "meta": {
    "request_id": "..."
  },
  "pagination": {
    "current_page": 1,
    "per_page": 15,
    "total": 100,
    "last_page": 7,
    "from": 1,
    "to": 15
  }
}
```

## Principais endpoints

### Auth

| Método | Endpoint | Descrição |
| --- | --- | --- |
| POST | `/api/v1/auth/register` | Cadastra usuário |
| POST | `/api/v1/auth/login` | Autentica usuário |
| POST | `/api/v1/auth/logout` | Revoga token atual |
| POST | `/api/v1/auth/logout-all` | Revoga todos os tokens |

### Perfil

| Método | Endpoint | Descrição |
| --- | --- | --- |
| GET | `/api/v1/me` | Retorna usuário autenticado |
| PATCH | `/api/v1/me` | Atualiza perfil |
| PATCH | `/api/v1/me/password` | Altera senha |

### Categorias

| Método | Endpoint |
| --- | --- |
| GET | `/api/v1/categories` |
| POST | `/api/v1/categories` |
| GET | `/api/v1/categories/{category}` |
| PATCH | `/api/v1/categories/{category}` |
| DELETE | `/api/v1/categories/{category}` |

### Formas de pagamento

| Método | Endpoint |
| --- | --- |
| GET | `/api/v1/payment-methods` |
| POST | `/api/v1/payment-methods` |
| GET | `/api/v1/payment-methods/{payment_method}` |
| PATCH | `/api/v1/payment-methods/{payment_method}` |
| DELETE | `/api/v1/payment-methods/{payment_method}` |

### Assinaturas

| Método | Endpoint |
| --- | --- |
| GET | `/api/v1/subscriptions` |
| POST | `/api/v1/subscriptions` |
| GET | `/api/v1/subscriptions/{subscription}` |
| PATCH | `/api/v1/subscriptions/{subscription}` |
| DELETE | `/api/v1/subscriptions/{subscription}` |
| PATCH | `/api/v1/subscriptions/{subscription}/pause` |
| PATCH | `/api/v1/subscriptions/{subscription}/resume` |
| PATCH | `/api/v1/subscriptions/{subscription}/cancel` |
| PATCH | `/api/v1/subscriptions/{subscription}/renew` |
| GET | `/api/v1/subscriptions/{subscription}/history` |

### Vencimentos

| Método | Endpoint |
| --- | --- |
| GET | `/api/v1/subscriptions/upcoming` |
| GET | `/api/v1/subscriptions/overdue` |

### Dashboard

| Método | Endpoint |
| --- | --- |
| GET | `/api/v1/dashboard/summary` |
| GET | `/api/v1/dashboard/monthly` |
| GET | `/api/v1/dashboard/yearly` |
| GET | `/api/v1/dashboard/by-category` |
| GET | `/api/v1/dashboard/by-payment-method` |

### Moedas

| Método | Endpoint |
| --- | --- |
| GET | `/api/v1/currencies/rates` |
| POST | `/api/v1/currencies/refresh` |
| GET | `/api/v1/currencies/convert` |

### Auditoria

| Método | Endpoint |
| --- | --- |
| GET | `/api/v1/audit-logs` |

## Variáveis de ambiente principais

| Variável | Descrição |
| --- | --- |
| `APP_NAME` | Nome da aplicação |
| `APP_ENV` | Ambiente |
| `APP_URL` | URL base |
| `DB_CONNECTION` | Driver do banco |
| `DB_HOST` | Host do banco |
| `DB_DATABASE` | Nome do banco |
| `DB_USERNAME` | Usuário do banco |
| `DB_PASSWORD` | Senha do banco |
| `REDIS_HOST` | Host do Redis |
| `MAIL_HOST` | Host SMTP local |
| `AWESOMEAPI_BASE_URL` | URL da AwesomeAPI |
| `CURRENCY_RATE_CACHE_MINUTES` | Tempo de cache de cotação |
| `API_VERSION` | Versão da API |
| `SCRAMBLE_API_PATH` | Prefixo documentado pelo OpenAPI |