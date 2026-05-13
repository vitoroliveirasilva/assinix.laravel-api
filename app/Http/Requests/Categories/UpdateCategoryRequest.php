<?php

namespace App\Http\Requests\Categories;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('category')) ?? false;
    }

    public function rules(): array
    {
        $category = $this->route('category');

        return [
            'name' => [
                'sometimes',
                'required',
                'string',
                'min:2',
                'max:100',
                Rule::unique('categories', 'name')
                    ->where(fn($query) => $query
                        ->where('user_id', $this->user()->id)
                        ->whereNull('deleted_at'))
                    ->ignore($category?->id),
            ],
            'description' => ['nullable', 'string', 'max:500'],
            'color' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'nome',
            'description' => 'descrição',
            'color' => 'cor',
            'is_active' => 'ativo',
        ];
    }
}