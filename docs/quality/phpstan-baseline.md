# PHPStan baseline

O primeiro uso do Larastan em um projeto Laravel já existente costuma apontar muitos problemas de tipagem estática relacionados a Eloquent, Resources, casts e propriedades dinâmicas.

No Assinix, os testes automatizados já validam o comportamento da aplicação. A baseline do PHPStan registra a dívida técnica atual e faz com que novos problemas passem a quebrar o CI.

## Gerar baseline

```bash
docker compose exec app vendor/bin/phpstan analyse --generate-baseline --memory-limit=1G
```

No PowerShell:

```powershell
.\scripts\phpstan-baseline.ps1
```

## Validar depois da baseline

```bash
docker compose exec app vendor/bin/phpstan analyse --memory-limit=1G
```

## Quando reduzir a baseline

Sempre que uma refatoração corrigir parte dos problemas atuais, rode:

```bash
docker compose exec app vendor/bin/phpstan analyse --generate-baseline --memory-limit=1G --allow-empty-baseline
```

Depois revise o diff do `phpstan-baseline.neon`.

## Importante

A baseline não serve como justificativa para negligenciar a qualidade. Trata-se de um retrato inicial da dívida técnica que permite priorizar correções: o objetivo é reduzir essa lista gradualmente, à medida que o código for melhorado.
