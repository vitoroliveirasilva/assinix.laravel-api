# ADR 0001: Usar Laravel Sanctum para autenticação

## Contexto

O Assinix é uma API REST própria, com autenticação baseada em tokens para usuários da própria aplicação.

## Decisão

Usar Laravel Sanctum para emissão e revogação de tokens pessoais.

## Motivos

- Integração nativa com Laravel.
- Menos complexidade que JWT.
- Permite revogar token atual.
- Permite revogar todos os tokens do usuário.
- Funciona bem com APIs próprias.
- Facilita testes automatizados com `Sanctum::actingAs`.

## Consequências

- O cliente deve enviar `Authorization: Bearer TOKEN`.
- Tokens ficam armazenados no banco.
- Revogação é simples e controlada.
