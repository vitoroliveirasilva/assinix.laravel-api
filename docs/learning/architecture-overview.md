# Visão geral da arquitetura

O Assinix usa uma arquitetura em camadas para manter o código organizado e testável.

## Fluxo principal

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
Model
  ↓
Resource
  ↓
ApiResponse
```

## Camadas

### Middleware

Cuida de comportamentos antes/depois da requisição:

- autenticação;
- usuário ativo;
- request_id;
- headers de segurança;
- auditoria.

### Form Request

Valida entrada da API.

Exemplo:

- `StoreSubscriptionRequest`
- `UpdatePasswordRequest`
- `ConvertCurrencyRequest`

### Controller

Recebe a requisição e chama o caso de uso correto.

Controller não deve concentrar regra de negócio.

### Action

Representa um caso de uso.

Exemplos:

- `CreateSubscriptionAction`
- `RenewSubscriptionAction`
- `BuildDashboardSummaryAction`

### Service

Contém regra reutilizável.

Exemplos:

- `CurrencyConversionService`
- `SubscriptionFinancialCalculator`
- `SubscriptionHistoryRecorder`

### Model

Representa tabela e relacionamento.

### Resource

Controla o JSON de saída.

### ApiResponse

Padroniza o envelope de resposta.
