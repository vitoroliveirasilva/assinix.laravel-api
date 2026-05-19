<?php

use Dedoc\Scramble\Http\Middleware\RestrictedDocsAccess;

$docsMiddleware = ['web'];

if (! in_array(env('APP_ENV', 'production'), ['local', 'testing'], true)) {
    $docsMiddleware[] = RestrictedDocsAccess::class;
}

return [
    /*
    |--------------------------------------------------------------------------
    | API Path
    |--------------------------------------------------------------------------
    |
    | Todas as rotas da API versionada ficam sob /api/v1.
    | O Scramble usará esse prefixo para encontrar os endpoints documentáveis.
    |
    */

    'api_path' => env('SCRAMBLE_API_PATH', 'api/v1'),

    /*
    |--------------------------------------------------------------------------
    | API Domain
    |--------------------------------------------------------------------------
    */

    'api_domain' => env('SCRAMBLE_API_DOMAIN'),

    /*
    |--------------------------------------------------------------------------
    | Informações exibidas na documentação
    |--------------------------------------------------------------------------
    */

    'info' => [
        'version' => env('API_VERSION', '1.0.0'),
        'description' => <<<'MARKDOWN'
Assinix é uma API REST profissional em PHP/Laravel para gerenciamento de assinaturas, vencimentos e gastos recorrentes.

A API contempla autenticação com Laravel Sanctum, CRUD de categorias, formas de pagamento e assinaturas, cálculo financeiro, dashboard, cotação de moedas, auditoria, logs estruturados e documentação OpenAPI.
MARKDOWN,
    ],

    /*
    |--------------------------------------------------------------------------
    | Servidores
    |--------------------------------------------------------------------------
    */

    'servers' => [
        'Local Docker' => env('APP_URL', 'http://localhost:8080').'/api/v1',
    ],

    /*
    |--------------------------------------------------------------------------
    | Middleware da documentação
    |--------------------------------------------------------------------------
    |
    | Em local/testing a documentação fica liberada para desenvolvimento e testes.
    | Nos demais ambientes, o RestrictedDocsAccess permanece ativo.
    |
    */

    'middleware' => $docsMiddleware,

    'extensions' => [],
];
