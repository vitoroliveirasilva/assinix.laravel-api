<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Categories\CreateCategoryAction;
use App\Actions\Categories\UpdateCategoryAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Categories\StoreCategoryRequest;
use App\Http\Requests\Categories\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Support\ApiResponse\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CategoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', Category::class);

        $perPage = $this->perPage($request);
        $search = trim((string) $request->query('search', ''));

        $categories = Category::query()
            ->forUser($request->user())
            ->when($search !== '', function ($query) use ($search): void {
                $query->whereRaw('LOWER(name) LIKE ?', ['%'.mb_strtolower($search).'%']);
            })
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();

        return ApiResponse::paginated(
            paginator: $categories,
            data: CategoryResource::collection($categories->getCollection())->resolve($request),
            message: 'Categorias retornadas com sucesso.',
        );
    }

    public function store(
        StoreCategoryRequest $request,
        CreateCategoryAction $action,
    ): JsonResponse {
        $category = $action->execute(
            user: $request->user(),
            data: $request->validated(),
        );

        return ApiResponse::success(
            data: [
                'category' => CategoryResource::make($category)->resolve($request),
            ],
            message: 'Categoria criada com sucesso.',
            status: 201,
        );
    }

    public function show(Request $request, Category $category): JsonResponse
    {
        Gate::authorize('view', $category);

        return ApiResponse::success(
            data: [
                'category' => CategoryResource::make($category)->resolve($request),
            ],
            message: 'Categoria retornada com sucesso.',
        );
    }

    public function update(
        UpdateCategoryRequest $request,
        Category $category,
        UpdateCategoryAction $action,
    ): JsonResponse {
        $category = $action->execute(
            category: $category,
            data: $request->validated(),
        );

        return ApiResponse::success(
            data: [
                'category' => CategoryResource::make($category)->resolve($request),
            ],
            message: 'Categoria atualizada com sucesso.',
        );
    }

    public function destroy(Request $request, Category $category): JsonResponse
    {
        Gate::authorize('delete', $category);

        $category->delete();

        return ApiResponse::success(
            message: 'Categoria removida com sucesso.',
        );
    }

    private function perPage(Request $request): int
    {
        $perPage = (int) $request->query('per_page', 15);

        return min(max($perPage, 1), 50);
    }
}
