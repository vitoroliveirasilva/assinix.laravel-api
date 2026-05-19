# Estilo de código

O Assinix usa Laravel Pint para padronizar estilo.

## Instalação

Em versões recentes do Laravel, o Pint normalmente já vem instalado. Se não estiver, rode:

```bash
docker compose exec app composer require laravel/pint --dev
```

## Verificar estilo sem alterar arquivos

```bash
docker compose exec app vendor/bin/pint --test
```

## Corrigir estilo automaticamente

```bash
docker compose exec app vendor/bin/pint
```

## Configuração

A configuração fica em:

```txt
pint.json
```

O preset usado é `laravel`.
