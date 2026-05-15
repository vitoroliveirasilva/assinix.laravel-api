# Estratégia de testes

O Assinix usa Pest para testes automatizados.

## Tipos de teste

### Feature tests

Testam endpoints reais da API.

Exemplos:

- autenticação;
- CRUD;
- dashboard;
- auditoria;
- health check.

### Unit tests

Testam regras isoladas.

Exemplo:

- `SubscriptionFinancialCalculatorTest`

## Banco nos testes

Os testes usam `RefreshDatabase` para isolar cada cenário.

## Integrações externas

Integrações externas devem usar fake/mock.

Exemplo:

```php
Http::fake([
    'https://economia.awesomeapi.com.br/*' => Http::response([...], 200),
]);
```

## O que sempre testar

- resposta de sucesso;
- validação;
- autorização;
- acesso cruzado entre usuários;
- efeitos no banco;
- auditoria, quando aplicável;
- formato JSON padronizado.
