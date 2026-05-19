<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\PaymentMethods\CreatePaymentMethodAction;
use App\Actions\PaymentMethods\UpdatePaymentMethodAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\PaymentMethods\StorePaymentMethodRequest;
use App\Http\Requests\PaymentMethods\UpdatePaymentMethodRequest;
use App\Http\Resources\PaymentMethodResource;
use App\Models\PaymentMethod;
use App\Support\ApiResponse\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PaymentMethodController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', PaymentMethod::class);

        $perPage = $this->perPage($request);
        $search = trim((string) $request->query('search', ''));

        $paymentMethods = PaymentMethod::query()
            ->forUser($request->user())
            ->when($search !== '', function ($query) use ($search): void {
                $query->whereRaw('LOWER(name) LIKE ?', ['%'.mb_strtolower($search).'%']);
            })
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();

        return ApiResponse::paginated(
            paginator: $paymentMethods,
            data: PaymentMethodResource::collection($paymentMethods->getCollection())->resolve($request),
            message: 'Formas de pagamento retornadas com sucesso.',
        );
    }

    public function store(
        StorePaymentMethodRequest $request,
        CreatePaymentMethodAction $action,
    ): JsonResponse {
        $paymentMethod = $action->execute(
            user: $request->user(),
            data: $request->validated(),
        );

        return ApiResponse::success(
            data: [
                'payment_method' => PaymentMethodResource::make($paymentMethod)->resolve($request),
            ],
            message: 'Forma de pagamento criada com sucesso.',
            status: 201,
        );
    }

    public function show(Request $request, PaymentMethod $paymentMethod): JsonResponse
    {
        Gate::authorize('view', $paymentMethod);

        return ApiResponse::success(
            data: [
                'payment_method' => PaymentMethodResource::make($paymentMethod)->resolve($request),
            ],
            message: 'Forma de pagamento retornada com sucesso.',
        );
    }

    public function update(
        UpdatePaymentMethodRequest $request,
        PaymentMethod $paymentMethod,
        UpdatePaymentMethodAction $action,
    ): JsonResponse {
        $paymentMethod = $action->execute(
            paymentMethod: $paymentMethod,
            data: $request->validated(),
        );

        return ApiResponse::success(
            data: [
                'payment_method' => PaymentMethodResource::make($paymentMethod)->resolve($request),
            ],
            message: 'Forma de pagamento atualizada com sucesso.',
        );
    }

    public function destroy(Request $request, PaymentMethod $paymentMethod): JsonResponse
    {
        Gate::authorize('delete', $paymentMethod);

        $paymentMethod->delete();

        return ApiResponse::success(
            message: 'Forma de pagamento removida com sucesso.',
        );
    }

    private function perPage(Request $request): int
    {
        $perPage = (int) $request->query('per_page', 15);

        return min(max($perPage, 1), 50);
    }
}
