# Regras de negócio de assinaturas

## Status

| Status | Entra no dashboard futuro? |
| --- | --- |
| active | Sim |
| paused | Não |
| canceled | Não |
| expired | Não |

## Recorrência

| Valor | Descrição |
| --- | --- |
| weekly | Semanal |
| monthly | Mensal |
| yearly | Anual |
| custom | Personalizada |

## Regras de validação

- Valor precisa ser maior que zero.
- Próximo vencimento não pode ser anterior à data inicial.
- Data final não pode ser anterior à data inicial.
- Recorrência custom exige `interval_in_days`.
- Categoria precisa pertencer ao usuário autenticado.
- Forma de pagamento precisa pertencer ao usuário autenticado.
- Cliente não pode informar `user_id` para criar assinatura em nome de outro usuário.

## Cálculo mensal estimado

```txt
weekly  = amount * 52 / 12 / interval
monthly = amount / interval
yearly  = amount / 12 / interval
custom  = amount * 30 / interval_in_days
```

## Cálculo anual previsto

```txt
weekly  = amount * 52 / interval
monthly = amount * 12 / interval
yearly  = amount / interval
custom  = amount * 365 / interval_in_days
```

## Moedas

O dashboard usa BRL como moeda base.

Se a assinatura estiver em moeda estrangeira:

1. busca cotação;
2. salva em `currency_rates`;
3. preenche `amount_brl`;
4. usa `amount_brl` nos cálculos.
