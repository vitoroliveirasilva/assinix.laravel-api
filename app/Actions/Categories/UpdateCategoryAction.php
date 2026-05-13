<?php

namespace App\Actions\Categories;

use App\Models\Category;

class UpdateCategoryAction
{
    public function execute(Category $category, array $data): Category
    {
        $category->fill([
            'name' => $data['name'] ?? $category->name,
            'description' => array_key_exists('description', $data)
                ? $data['description']
                : $category->description,
            'color' => array_key_exists('color', $data)
                ? $data['color']
                : $category->color,
            'is_active' => array_key_exists('is_active', $data)
                ? $data['is_active']
                : $category->is_active,
        ]);

        $category->save();

        return $category->refresh();
    }
}