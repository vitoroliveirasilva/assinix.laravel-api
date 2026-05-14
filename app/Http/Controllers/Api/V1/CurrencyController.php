<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Currency\ConvertCurrencyAction;
use App\Actions\Currency\ListCurrencyRatesAction;
use App\Actions\Currency\RefreshCurrencyRateAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Currency\ConvertCurrencyRequest;
use App\Http\Requests\Currency\CurrencyRateIndexRequest;
use App\Http\Requests\Currency\RefreshCurrencyRateRequest;
use App\Http\Resources\CurrencyRateResource;
use App\Support\ApiResponse\ApiResponse;
use Illuminate\Http\JsonResponse;

class CurrencyController extends Controller
{
    public function rates(
        CurrencyRateIndexRequest $request,
        ListCurrencyRatesAction $action,
    ): JsonResponse {
        $rates = $action->execute($request->validated());

        return ApiResponse::paginated(
            paginator: $rates,
            data: CurrencyRateResource::collection($rates->getCollection())->resolve($request),
            message: 'Cotações retornadas com sucesso.',
        );
    }

    public function refresh(
        RefreshCurrencyRateRequest $request,
        RefreshCurrencyRateAction $action,
    ): JsonResponse {
        $result = $action->execute(
            user: $request->user(),
            data: $request->validated(),
        );

        return ApiResponse::success(
            data: [
                'rate' => CurrencyRateResource::make($result['rate'])->resolve($request),
                'conversion_source' => $result['conversion_source'],
                'updated_subscriptions_count' => $result['updated_subscriptions_count'],
            ],
            message: 'Cotação atualizada com sucesso.',
        );
    }

    public function convert(
        ConvertCurrencyRequest $request,
        ConvertCurrencyAction $action,
    ): JsonResponse {
        $result = $action->execute($request->validated());

        return ApiResponse::success(
            data: [
                'conversion' => [
                    'original_amount' => number_format($result['original_amount'], 2, '.', ''),
                    'converted_amount' => number_format($result['converted_amount'], 2, '.', ''),
                    'from_currency' => $result['from_currency']->value,
                    'to_currency' => $result['to_currency']->value,
                    'rate' => number_format($result['rate'], 8, '.', ''),
                    'conversion_source' => $result['conversion_source'],
                    'quoted_at' => $result['quoted_at']?->toISOString(),
                ],
            ],
            message: 'Conversão realizada com sucesso.',
        );
    }
}