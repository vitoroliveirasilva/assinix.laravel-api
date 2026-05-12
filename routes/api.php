<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'success' => true,
        'message' => 'Assinix API is running.',
        'data' => [
            'name' => config('app.name'),
            'version' => config('app.version', 'v1'),
        ],
        'errors' => null,
        'meta' => [
            'request_id' => request()->attributes->get('request_id'),
        ],
    ]);
});