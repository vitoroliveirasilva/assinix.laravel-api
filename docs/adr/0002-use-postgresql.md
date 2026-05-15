# ADR 0002: Usar PostgreSQL como banco principal

## Contexto

O Assinix precisa armazenar usuários, categorias, formas de pagamento, assinaturas, histórico, auditoria e cotações.

## Decisão

Usar PostgreSQL como banco relacional principal.

## Motivos

- Suporte a integridade relacional.
- Bom suporte a índices.
- Suporte a JSON quando necessário.
- Ótimo para queries analíticas futuras.
- Bom encaixe com Docker e ambiente local.
- Mais robusto para portfólio back-end do que SQLite em produção.

## Consequências

- Ambiente local usa container PostgreSQL.
- Testes podem usar SQLite em memória para velocidade.
- Migrations precisam respeitar tipos e constraints compatíveis.
