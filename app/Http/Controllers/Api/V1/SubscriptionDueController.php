<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\SubscriptionStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Subscriptions\SubscriptionDueQueryRequest;
use App\Http\Resources\SubscriptionResource;
use App\Models\Subscription;
use App\Support\ApiResponse\ApiResponse;
use Illuminate\Http\JsonResponse;

class SubscriptionDueController extends Controller
{
    public function upcoming(SubscriptionDueQueryRequest $request): JsonResponse
    {
        $today = now()->toDateString();
        $limitDate = now()->addDays($request->days())->toDateString();

        $subscriptions = Subscription::query()
            ->with(['category', 'paymentMethod'])
            ->forUser($request->user())
            ->where('status', SubscriptionStatus::Active)
            ->whereDate('next_billing_at', '>=', $today)
            ->whereDate('next_billing_at', '<=', $limitDate)
            ->orderBy('next_billing_at')
            ->orderBy('name')
            ->paginate($request->perPage())
            ->withQueryString();

        return ApiResponse::paginated(
            paginator: $subscriptions,
            data: SubscriptionResource::collection($subscriptions->getCollection())->resolve($request),
            message: 'Próximos vencimentos retornados com sucesso.',
            meta: [
                'days' => $request->days(),
                'from' => $today,
                'to' => $limitDate,
            ],
        );
    }

    public function overdue(SubscriptionDueQueryRequest $request): JsonResponse
    {
        $today = now()->toDateString();

        $subscriptions = Subscription::query()
            ->with(['category', 'paymentMethod'])
            ->forUser($request->user())
            ->where('status', SubscriptionStatus::Active)
            ->whereDate('next_billing_at', '<', $today)
            ->orderBy('next_billing_at')
            ->orderBy('name')
            ->paginate($request->perPage())
            ->withQueryString();

        return ApiResponse::paginated(
            paginator: $subscriptions,
            data: SubscriptionResource::collection($subscriptions->getCollection())->resolve($request),
            message: 'Assinaturas vencidas retornadas com sucesso.',
            meta: [
                'before' => $today,
            ],
        );
    }
}