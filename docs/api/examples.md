# Exemplos de uso da API

## 1. Registrar usuário

```bash
curl -X POST http://localhost:8080/api/v1/auth/register \
  -H "Content-Type: application/json" \
  -d "{\"name\":\"Vitor Oliveira\",\"email\":\"vitor@example.com\",\"password\":\"Password123\",\"password_confirmation\":\"Password123\",\"device_name\":\"Local\"}"
```

## 2. Login

```bash
curl -X POST http://localhost:8080/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d "{\"email\":\"vitor@example.com\",\"password\":\"Password123\",\"device_name\":\"Local\"}"
```

## 3. Consultar usuário autenticado

```bash
curl http://localhost:8080/api/v1/me \
  -H "Authorization: Bearer SEU_TOKEN"
```

## 4. Criar categoria

```bash
curl -X POST http://localhost:8080/api/v1/categories \
  -H "Authorization: Bearer SEU_TOKEN" \
  -H "Content-Type: application/json" \
  -d "{\"name\":\"Streaming\",\"description\":\"Serviços recorrentes de streaming\",\"color\":\"#EF4444\"}"
```

## 5. Criar forma de pagamento

```bash
curl -X POST http://localhost:8080/api/v1/payment-methods \
  -H "Authorization: Bearer SEU_TOKEN" \
  -H "Content-Type: application/json" \
  -d "{\"name\":\"Cartão principal\",\"type\":\"credit_card\",\"brand\":\"Visa\",\"last_four\":\"1234\"}"
```

## 6. Criar assinatura em BRL

```bash
curl -X POST http://localhost:8080/api/v1/subscriptions \
  -H "Authorization: Bearer SEU_TOKEN" \
  -H "Content-Type: application/json" \
  -d "{\"name\":\"Netflix\",\"amount\":55.90,\"currency\":\"BRL\",\"recurrence\":\"monthly\",\"interval\":1,\"starts_at\":\"2026-05-14\",\"next_billing_at\":\"2026-06-14\"}"
```

## 7. Criar assinatura em USD

```bash
curl -X POST http://localhost:8080/api/v1/subscriptions \
  -H "Authorization: Bearer SEU_TOKEN" \
  -H "Content-Type: application/json" \
  -d "{\"name\":\"GitHub Copilot\",\"amount\":10,\"currency\":\"USD\",\"recurrence\":\"monthly\",\"interval\":1,\"starts_at\":\"2026-05-14\",\"next_billing_at\":\"2026-06-14\"}"
```

## 8. Dashboard financeiro

```bash
curl http://localhost:8080/api/v1/dashboard/summary \
  -H "Authorization: Bearer SEU_TOKEN"
```

## 9. Converter moeda

```bash
curl "http://localhost:8080/api/v1/currencies/convert?amount=10&from_currency=USD&to_currency=BRL" \
  -H "Authorization: Bearer SEU_TOKEN"
```

## 10. Auditoria

```bash
curl http://localhost:8080/api/v1/audit-logs \
  -H "Authorization: Bearer SEU_TOKEN"
```
