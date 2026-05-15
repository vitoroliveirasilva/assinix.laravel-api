# Ciclo de vida de uma requisição

Exemplo: criar assinatura.

```txt
POST /api/v1/subscriptions
```

## 1. Middleware

A requisição passa por middlewares globais e de rota:

- `ForceJsonResponse`
- `RequestId`
- `SecurityHeaders`
- `AuditRequest`
- `auth:sanctum`
- `active`
- `throttle:api.authenticated`

## 2. Form Request

`StoreSubscriptionRequest` valida:

- nome;
- valor;
- moeda;
- recorrência;
- datas;
- categoria do usuário;
- forma de pagamento do usuário.

## 3. Controller

`SubscriptionController@store` recebe dados validados e chama `CreateSubscriptionAction`.

## 4. Action

`CreateSubscriptionAction` cria a assinatura e registra histórico.

## 5. Service

Se a assinatura for em moeda estrangeira, `CurrencyConversionService` converte para BRL.

## 6. Model

`Subscription` é persistida no banco.

## 7. Resource

`SubscriptionResource` monta o JSON público.

## 8. ApiResponse

A resposta final segue o padrão:

```json
{
  "success": true,
  "message": "...",
  "data": {},
  "errors": null,
  "meta": {
    "request_id": "..."
  }
}
```

## 9. Auditoria

Após resposta bem-sucedida, `AuditRequest` registra a ação em `audit_logs`.
