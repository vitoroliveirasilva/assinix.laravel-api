<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Subscriptions\CancelSubscriptionAction;
use App\Actions\Subscriptions\CreateSubscriptionAction;
use App\Actions\Subscriptions\PauseSubscriptionAction;
use App\Actions\Subscriptions\RenewSubscriptionAction;
use App\Actions\Subscriptions\ResumeSubscriptionAction;
use App\Actions\Subscriptions\UpdateSubscriptionAction;
use App\Enums\RecurrenceType;
use App\Enums\SubscriptionStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Subscriptions\RenewSubscriptionRequest;
use App\Http\Requests\Subscriptions\StoreSubscriptionRequest;
use App\Http\Requests\Subscriptions\UpdateSubscriptionRequest;
use App\Http\Resources\SubscriptionResource;
use App\Models\Subscription;
use App\Support\ApiResponse\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class SubscriptionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', Subscription::class);

        $validated = validator($request->query(), [
            'search' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', Rule::enum(SubscriptionStatus::class)],
            'recurrence' => ['nullable', Rule::enum(RecurrenceType::class)],
            'category_id' => ['nullable', 'integer'],
            'payment_method_id' => ['nullable', 'integer'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ])->validate();

        $search = trim((string) ($validated['search'] ?? ''));
        $perPage = (int) ($validated['per_page'] ?? 15);

        $subscriptions = Subscription::query()
            ->with(['category', 'paymentMethod'])
            ->forUser($request->user())
            ->when($search !== '', function ($query) use ($search): void {
                $query->whereRaw('LOWER(name) LIKE ?', ['%' . mb_strtolower($search) . '%']);
            })
            ->when(isset($validated['status']), fn($query) => $query->where('status', $validated['status']))
            ->when(isset($validated['recurrence']), fn($query) => $query->where('recurrence', $validated['recurrence']))
            ->when(isset($validated['category_id']), fn($query) => $query->where('category_id', $validated['category_id']))
            ->when(isset($validated['payment_method_id']), fn($query) => $query->where('payment_method_id', $validated['payment_method_id']))
            ->orderBy('next_billing_at')
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();

        return ApiResponse::paginated(
            paginator: $subscriptions,
            data: SubscriptionResource::collection($subscriptions->getCollection())->resolve($request),
            message: 'Assinaturas retornadas com sucesso.',
        );
    }

    public function store(
        StoreSubscriptionRequest $request,
        CreateSubscriptionAction $action,
    ): JsonResponse {
        $subscription = $action->execute(
            user: $request->user(),
            data: $request->validated(),
        );

        return ApiResponse::success(
            data: [
                'subscription' => SubscriptionResource::make($subscription)->resolve($request),
            ],
            message: 'Assinatura criada com sucesso.',
            status: 201,
        );
    }

    public function show(Request $request, Subscription $subscription): JsonResponse
    {
        Gate::authorize('view', $subscription);

        $subscription->load(['category', 'paymentMethod']);

        return ApiResponse::success(
            data: [
                'subscription' => SubscriptionResource::make($subscription)->resolve($request),
            ],
            message: 'Assinatura retornada com sucesso.',
        );
    }

    public function update(
        UpdateSubscriptionRequest $request,
        Subscription $subscription,
        UpdateSubscriptionAction $action,
    ): JsonResponse {
        $subscription = $action->execute(
            subscription: $subscription,
            user: $request->user(),
            data: $request->validated(),
        );

        return ApiResponse::success(
            data: [
                'subscription' => SubscriptionResource::make($subscription)->resolve($request),
            ],
            message: 'Assinatura atualizada com sucesso.',
        );
    }

    public function destroy(Request $request, Subscription $subscription): JsonResponse
    {
        Gate::authorize('delete', $subscription);

        $subscription->delete();

        return ApiResponse::success(
            message: 'Assinatura removida com sucesso.',
        );
    }

    public function pause(
        Request $request,
        Subscription $subscription,
        PauseSubscriptionAction $action,
    ): JsonResponse {
        Gate::authorize('pause', $subscription);

        $subscription = $action->execute(
            subscription: $subscription,
            user: $request->user(),
        );

        return ApiResponse::success(
            data: [
                'subscription' => SubscriptionResource::make($subscription)->resolve($request),
            ],
            message: 'Assinatura pausada com sucesso.',
        );
    }

    public function resume(
        Request $request,
        Subscription $subscription,
        ResumeSubscriptionAction $action,
    ): JsonResponse {
        Gate::authorize('resume', $subscription);

        $subscription = $action->execute(
            subscription: $subscription,
            user: $request->user(),
        );

        return ApiResponse::success(
            data: [
                'subscription' => SubscriptionResource::make($subscription)->resolve($request),
            ],
            message: 'Assinatura retomada com sucesso.',
        );
    }

    public function cancel(
        Request $request,
        Subscription $subscription,
        CancelSubscriptionAction $action,
    ): JsonResponse {
        Gate::authorize('cancel', $subscription);

        $subscription = $action->execute(
            subscription: $subscription,
            user: $request->user(),
        );

        return ApiResponse::success(
            data: [
                'subscription' => SubscriptionResource::make($subscription)->resolve($request),
            ],
            message: 'Assinatura cancelada com sucesso.',
        );
    }

    public function renew(
        RenewSubscriptionRequest $request,
        Subscription $subscription,
        RenewSubscriptionAction $action,
    ): JsonResponse {
        $subscription = $action->execute(
            subscription: $subscription,
            user: $request->user(),
            data: $request->validated(),
        );

        return ApiResponse::success(
            data: [
                'subscription' => SubscriptionResource::make($subscription)->resolve($request),
            ],
            message: 'Assinatura renovada com sucesso.',
        );
    }
}