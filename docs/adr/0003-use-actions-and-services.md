# ADR 0003: Usar Actions e Services para regras de aplicação

## Contexto

Controllers grandes dificultam manutenção, testes e evolução.

## Decisão

Usar Controllers finos, Actions para casos de uso e Services para regras reutilizáveis.

## Motivos

- Controller cuida apenas de HTTP.
- Action representa um caso de uso.
- Service concentra regra reutilizável.
- Testes ficam mais simples.
- Código fica mais fácil de explicar em portfólio.

## Exemplo

Criar assinatura:

```txt
SubscriptionController
  ↓
StoreSubscriptionRequest
  ↓
CreateSubscriptionAction
  ↓
CurrencyConversionService
  ↓
SubscriptionHistoryRecorder
```

## Consequências

- Mais arquivos no projeto.
- Mais clareza de responsabilidade.
- Menos lógica acoplada em Controller.
