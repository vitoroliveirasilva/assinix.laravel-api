<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\SubscriptionHistoryResource;
use App\Models\Subscription;
use App\Support\ApiResponse\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class SubscriptionHistoryController extends Controller
{
    public function index(Request $request, Subscription $subscription): JsonResponse
    {
        Gate::authorize('viewHistory', $subscription);

        $perPage = min(max((int) $request->query('per_page', 15), 1), 50);

        $histories = $subscription
            ->histories()
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        return ApiResponse::paginated(
            paginator: $histories,
            data: SubscriptionHistoryResource::collection($histories->getCollection())->resolve($request),
            message: 'Histórico da assinatura retornado com sucesso.',
        );
    }
}