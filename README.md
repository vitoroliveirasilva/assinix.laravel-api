# Assinix Laravel API

![PHP](https://img.shields.io/badge/PHP-8.4-777BB4?style=flat-square&logo=php&logoColor=white)
![Laravel](https://img.shields.io/badge/Laravel-13-FF2D20?style=flat-square&logo=laravel&logoColor=white)
![PostgreSQL](https://img.shields.io/badge/PostgreSQL-17-4169E1?style=flat-square&logo=postgresql&logoColor=white)
![Redis](https://img.shields.io/badge/Redis-7.4-DC382D?style=flat-square&logo=redis&logoColor=white)
![Docker](https://img.shields.io/badge/Docker-ready-2496ED?style=flat-square&logo=docker&logoColor=white)
![OpenAPI](https://img.shields.io/badge/OpenAPI-3.1-6BA539?style=flat-square&logo=openapiinitiative&logoColor=white)
![License](https://img.shields.io/badge/license-MIT-green?style=flat-square)

API REST profissional em PHP/Laravel para gerenciamento de assinaturas, vencimentos e gastos recorrentes.

O **Assinix** permite que usuários cadastrem serviços recorrentes como streaming, internet, faculdade, servidores, ferramentas SaaS, academia, cursos, hospedagem, domínios e outros gastos semanais, mensais, anuais ou personalizados.

## Objetivo

Este projeto foi desenvolvido como portfólio back-end com foco em:

- arquitetura limpa;
- Laravel moderno;
- API REST versionada;
- banco relacional;
- autenticação e autorização;
- regras de negócio reais;
- testes automatizados;
- Docker;
- documentação OpenAPI;
- logs e auditoria;
- integração externa;
- CI/CD e qualidade de código.

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

- Autenticação com Laravel Sanctum.
- Cadastro, login, logout e logout-all.
- Consulta e atualização de perfil.
- Alteração de senha.
- Bloqueio de usuário inativo.
- Rate limit para autenticação.
- CRUD de categorias.
- CRUD de formas de pagamento.
- CRUD de assinaturas.
- Status de assinatura: `active`, `paused`, `canceled`, `expired`.
- Recorrência: `weekly`, `monthly`, `yearly`, `custom`.
- Próximos vencimentos.
- Assinaturas vencidas.
- Dashboard financeiro.
- Projeção mensal.
- Projeção anual.
- Agrupamento por categoria.
- Agrupamento por forma de pagamento.
- Conversão de moedas para BRL.
- Cache de cotações.
- Fallback para última cotação conhecida.
- Histórico de alterações em assinaturas.
- Auditoria de ações importantes.
- Logs estruturados com `request_id`.
- Health check completo.
- Documentação OpenAPI/Swagger.
- Testes automatizados.
- Pipeline de qualidade.

## Arquitetura

O Assinix segue uma arquitetura em camadas, mantendo Controllers e regras de negócio fora da camada HTTP.

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

### Responsabilidades

| Camada | Responsabilidade |
| --- | --- |
| Middleware | Regras transversais, autenticação, headers, auditoria e request_id |
| Form Request | Validação e autorização inicial dos dados de entrada |
| Controller | Entrada HTTP e orquestração simples |
| Action | Caso de uso específico da aplicação |
| Service | Regra reutilizável e lógica de domínio |
| Model | Persistência e relacionamentos |
| Resource | Transformação da resposta JSON |
| ApiResponse | Envelope padronizado de resposta |

## Estrutura principal

```txt
app/
  Actions/
    Auth/
    Categories/
    Currency/
    Dashboard/
    PaymentMethods/
    Subscriptions/
  Enums/
  Http/
    Controllers/
      Api/
        V1/
    Middleware/
    Requests/
    Resources/
  Models/
  Policies/
  Services/
    Audit/
    Currency/
    Subscription/
  Support/
    ApiResponse/
    Logging/
    RequestContext/

database/
  factories/
  migrations/
  seeders/

docs/
  adr/
  api/
  learning/

docker/
  nginx/
  php/

routes/
  api.php
  health.php

tests/
  Feature/
  Unit/
```

## Regras de negócio principais

- Usuário só acessa os próprios dados.
- Nenhum endpoint aceita `user_id` livre para criar recurso em nome de outro usuário.
- Valor da assinatura precisa ser maior que zero.
- Próximo vencimento não pode ser anterior à data inicial.
- Data final, se existir, não pode ser anterior à data inicial.
- Assinaturas ativas entram nos cálculos financeiros.
- Assinaturas pausadas, canceladas ou expiradas não entram em projeções futuras.
- Próximos vencimentos consideram apenas assinaturas ativas.
- Dashboard financeiro usa BRL como moeda base.
- Assinaturas em moeda estrangeira são convertidas para BRL.
- Se a API externa de cotação falhar, a aplicação usa a última cotação conhecida.
- Se não houver cotação conhecida, retorna erro controlado.
- Alterações importantes geram histórico.
- Ações sensíveis geram auditoria.

## Fórmulas financeiras

### Gasto mensal estimado

```txt
weekly  = amount * 52 / 12 / interval
monthly = amount / interval
yearly  = amount / 12 / interval
custom  = amount * 30 / interval_in_days
```

### Gasto anual previsto

```txt
weekly  = amount * 52 / interval
monthly = amount * 12 / interval
yearly  = amount / interval
custom  = amount * 365 / interval_in_days
```

## Requisitos

- Docker
- Docker Compose
- Git
- GitHub CLI, opcional para release

## Subindo o ambiente

Clone o projeto:

```bash
git clone https://github.com/vitoroliveirasilva/assinix.laravel-api.git
cd assinix.laravel-api
```

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

## Padrão de resposta

### Sucesso

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

### Erro

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

### Resposta paginada

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

## Autenticação

A API usa Laravel Sanctum com Bearer Token.

Header:

```http
Authorization: Bearer {TOKEN}
```

Fluxo básico:

```txt
POST /api/v1/auth/register
  ↓
Recebe token
  ↓
Usa Authorization: Bearer TOKEN
  ↓
Acessa rotas protegidas
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

| Método | Endpoint | Descrição |
| --- | --- | --- |
| GET | `/api/v1/categories` | Lista categorias |
| POST | `/api/v1/categories` | Cria categoria |
| GET | `/api/v1/categories/{category}` | Detalha categoria |
| PATCH | `/api/v1/categories/{category}` | Atualiza categoria |
| DELETE | `/api/v1/categories/{category}` | Remove categoria |

### Formas de pagamento

| Método | Endpoint | Descrição |
| --- | --- | --- |
| GET | `/api/v1/payment-methods` | Lista formas de pagamento |
| POST | `/api/v1/payment-methods` | Cria forma de pagamento |
| GET | `/api/v1/payment-methods/{payment_method}` | Detalha forma de pagamento |
| PATCH | `/api/v1/payment-methods/{payment_method}` | Atualiza forma de pagamento |
| DELETE | `/api/v1/payment-methods/{payment_method}` | Remove forma de pagamento |

### Assinaturas

| Método | Endpoint | Descrição |
| --- | --- | --- |
| GET | `/api/v1/subscriptions` | Lista assinaturas |
| POST | `/api/v1/subscriptions` | Cria assinatura |
| GET | `/api/v1/subscriptions/{subscription}` | Detalha assinatura |
| PATCH | `/api/v1/subscriptions/{subscription}` | Atualiza assinatura |
| DELETE | `/api/v1/subscriptions/{subscription}` | Remove assinatura |
| PATCH | `/api/v1/subscriptions/{subscription}/pause` | Pausa assinatura |
| PATCH | `/api/v1/subscriptions/{subscription}/resume` | Retoma assinatura |
| PATCH | `/api/v1/subscriptions/{subscription}/cancel` | Cancela assinatura |
| PATCH | `/api/v1/subscriptions/{subscription}/renew` | Renova assinatura |
| GET | `/api/v1/subscriptions/{subscription}/history` | Lista histórico da assinatura |

### Vencimentos

| Método | Endpoint | Descrição |
| --- | --- | --- |
| GET | `/api/v1/subscriptions/upcoming` | Lista próximos vencimentos |
| GET | `/api/v1/subscriptions/overdue` | Lista assinaturas vencidas |

### Dashboard

| Método | Endpoint | Descrição |
| --- | --- | --- |
| GET | `/api/v1/dashboard/summary` | Resumo financeiro |
| GET | `/api/v1/dashboard/monthly` | Projeção mensal |
| GET | `/api/v1/dashboard/yearly` | Projeção anual |
| GET | `/api/v1/dashboard/by-category` | Agrupamento por categoria |
| GET | `/api/v1/dashboard/by-payment-method` | Agrupamento por forma de pagamento |

### Moedas

| Método | Endpoint | Descrição |
| --- | --- | --- |
| GET | `/api/v1/currencies/rates` | Lista cotações salvas |
| POST | `/api/v1/currencies/refresh` | Atualiza cotação |
| GET | `/api/v1/currencies/convert` | Converte valor entre moedas |

### Auditoria

| Método | Endpoint | Descrição |
| --- | --- | --- |
| GET | `/api/v1/audit-logs` | Lista logs de auditoria do usuário |

### Health

| Método | Endpoint | Descrição |
| --- | --- | --- |
| GET | `/health` | Health check público |
| GET | `/api/v1/health` | Health check versionado |

## Documentação OpenAPI

O projeto usa Scramble para gerar documentação OpenAPI.

Acessar documentação interativa:

```txt
http://localhost:8080/docs/api
```

Acessar OpenAPI JSON:

```txt
http://localhost:8080/docs/api.json
```

Exportar specification:

```bash
docker compose exec app php artisan scramble:export --path=public/openapi.json
```

## Testes

Rodar todos os testes:

```bash
docker compose exec app php artisan test
```

Rodar testes específicos:

```bash
docker compose exec app php artisan test --filter=AuthTest
docker compose exec app php artisan test --filter=CategoryCrudTest
docker compose exec app php artisan test --filter=PaymentMethodCrudTest
docker compose exec app php artisan test --filter=SubscriptionCrudTest
docker compose exec app php artisan test --filter=DashboardTest
docker compose exec app php artisan test --filter=CurrencyRateTest
docker compose exec app php artisan test --filter=AuditLogTest
```

## Qualidade de código

Rodar todos os testes via Composer:

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

Rodar análise estática com Larastan/PHPStan:

```bash
docker compose exec app composer analyse
```

Rodar a esteira local completa:

```bash
docker compose exec app composer quality
```

Exportar OpenAPI via Composer:

```bash
docker compose exec app composer openapi:export
```

## CI/CD

O projeto possui pipeline de qualidade com GitHub Actions.

A esteira valida:

- instalação de dependências via Composer;
- preparação do Laravel;
- migrations em PostgreSQL;
- execução dos testes;
- validação de estilo com Laravel Pint;
- análise estática com Larastan/PHPStan;
- exportação da documentação OpenAPI;
- validação de build Docker.

O objetivo é garantir que o projeto continue saudável a cada push ou pull request.

## Variáveis de ambiente principais

| Variável | Descrição |
| --- | --- |
| `APP_NAME` | Nome da aplicação |
| `APP_ENV` | Ambiente |
| `APP_KEY` | Chave da aplicação |
| `APP_URL` | URL base |
| `DB_CONNECTION` | Driver do banco |
| `DB_HOST` | Host do banco |
| `DB_PORT` | Porta do banco |
| `DB_DATABASE` | Nome do banco |
| `DB_USERNAME` | Usuário do banco |
| `DB_PASSWORD` | Senha do banco |
| `REDIS_HOST` | Host do Redis |
| `REDIS_PORT` | Porta do Redis |
| `MAIL_HOST` | Host SMTP local |
| `AWESOMEAPI_BASE_URL` | URL da AwesomeAPI |
| `CURRENCY_RATE_CACHE_MINUTES` | Tempo de cache de cotação |
| `API_VERSION` | Versão da API |
| `SCRAMBLE_API_PATH` | Prefixo documentado pelo OpenAPI |

## Documentação complementar

| Documento | Descrição |
| --- | --- |
| `docs/api/examples.md` | Exemplos de requisições |
| `docs/api/openapi.md` | Guia da documentação OpenAPI |
| `docs/adr/` | Decisões de arquitetura |
| `docs/learning/` | Explicações didáticas sobre arquitetura, auth, regras e testes |

## Melhorias futuras

- Implementar lembretes de vencimento por e-mail.
- Criar jobs para atualização automática de cotações.
- Adicionar roles administrativas.
- Criar painel administrativo separado.
- Adicionar suporte a múltiplas moedas base.
- Adicionar exportação CSV/Excel dos relatórios.
- Adicionar filtros avançados no dashboard.
- Criar endpoint para comparação mês a mês.
- Melhorar o baseline do PHPStan gradualmente.
- Subir o nível do PHPStan/Larastan.
- Adicionar mutation testing.
- Adicionar cache Docker no CI.
- Publicar collection do Postman/Insomnia.
- Criar deploy em VPS ou serviço cloud.
- Adicionar monitoramento com Sentry ou ferramenta similar