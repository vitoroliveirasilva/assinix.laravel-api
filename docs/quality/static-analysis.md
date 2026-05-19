# Análise estática

O Assinix usa Larastan, uma extensão do PHPStan para Laravel.

## Instalação

```bash
docker compose exec app composer require --dev "larastan/larastan:^3.0" --with-all-dependencies
```

## Rodar análise

```bash
docker compose exec app vendor/bin/phpstan analyse --memory-limit=1G
```

## Configuração

A configuração fica em:

```txt
phpstan.neon.dist
```

## Nível inicial

O projeto começa no nível `4`.

A ideia é aumentar gradualmente conforme a base estabilizar:

```txt
level 4 -> level 5 -> level 6 -> ...
```

Não é obrigatório começar no nível máximo. O importante é ter uma régua objetiva e evolutiva.
