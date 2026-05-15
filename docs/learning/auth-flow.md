# Fluxo de autenticação

O Assinix usa Laravel Sanctum.

## Cadastro

```txt
POST /api/v1/auth/register
  ↓
RegisterRequest
  ↓
RegisterUserAction
  ↓
User criado
  ↓
Token Sanctum emitido
```

## Login

```txt
POST /api/v1/auth/login
  ↓
LoginRequest
  ↓
LoginUserAction
  ↓
Hash::check
  ↓
verifica is_active
  ↓
Token Sanctum emitido
```

## Requisições autenticadas

O cliente envia:

```http
Authorization: Bearer TOKEN
```

## Logout

Revoga apenas o token atual:

```txt
POST /api/v1/auth/logout
```

## Logout all

Revoga todos os tokens do usuário:

```txt
POST /api/v1/auth/logout-all
```

## Usuário inativo

Mesmo com token válido, o middleware `active` bloqueia acesso de usuário inativo.
