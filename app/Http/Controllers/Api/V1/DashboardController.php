<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Dashboard\BuildDashboardByCategoryAction;
use App\Actions\Dashboard\BuildDashboardByPaymentMethodAction;
use App\Actions\Dashboard\BuildDashboardMonthlyAction;
use App\Actions\Dashboard\BuildDashboardSummaryAction;
use App\Actions\Dashboard\BuildDashboardYearlyAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\DashboardQueryRequest;
use App\Support\ApiResponse\ApiResponse;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function summary(
        DashboardQueryRequest $request,
        BuildDashboardSummaryAction $action,
    ): JsonResponse {
        return ApiResponse::success(
            data: [
                'summary' => $action->execute($request->user()),
            ],
            message: 'Resumo financeiro retornado com sucesso.',
        );
    }

    public function monthly(
        DashboardQueryRequest $request,
        BuildDashboardMonthlyAction $action,
    ): JsonResponse {
        return ApiResponse::success(
            data: [
                'months' => $action->execute(
                    user: $request->user(),
                    months: $request->months(),
                ),
            ],
            message: 'Dashboard mensal retornado com sucesso.',
        );
    }

    public function yearly(
        DashboardQueryRequest $request,
        BuildDashboardYearlyAction $action,
    ): JsonResponse {
        return ApiResponse::success(
            data: [
                'years' => $action->execute(
                    user: $request->user(),
                    years: $request->years(),
                ),
            ],
            message: 'Dashboard anual retornado com sucesso.',
        );
    }

    public function byCategory(
        DashboardQueryRequest $request,
        BuildDashboardByCategoryAction $action,
    ): JsonResponse {
        return ApiResponse::success(
            data: [
                'categories' => $action->execute($request->user()),
            ],
            message: 'Dashboard por categoria retornado com sucesso.',
        );
    }

    public function byPaymentMethod(
        DashboardQueryRequest $request,
        BuildDashboardByPaymentMethodAction $action,
    ): JsonResponse {
        return ApiResponse::success(
            data: [
                'payment_methods' => $action->execute($request->user()),
            ],
            message: 'Dashboard por forma de pagamento retornado com sucesso.',
        );
    }
}