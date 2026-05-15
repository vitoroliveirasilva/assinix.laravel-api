# ADR 0004: Usar AwesomeAPI para cotação de moedas

## Contexto

O Assinix permite cadastrar assinaturas em moedas estrangeiras e precisa converter valores para BRL no dashboard.

## Decisão

Usar AwesomeAPI como provedor inicial de cotações.

## Motivos

- API simples para pares como USD-BRL e EUR-BRL.
- Retorno JSON direto.
- Boa o suficiente para meu projeto de portfólio.
- Integração fácil com Laravel HTTP Client.
- Permite testes com fake/mock sem depender da API real.

## Estratégia

- Buscar cotação apenas quando necessário.
- Cachear resultado em `currency_rates`.
- Usar última cotação conhecida se a API falhar.
- Retornar erro controlado se não houver cotação disponível.

## Consequências

- A aplicação depende de um provedor externo para novas cotações.
- É necessário tratar falhas de rede.
- O dashboard usa apenas valores já convertidos em BRL.
