<?php

namespace App\Actions\Categories;

use App\Models\Category;
use App\Models\User;

class CreateCategoryAction
{
    public function execute(User $user, array $data): Category
    {
        return $user->categories()->create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'color' => $data['color'] ?? null,
            'is_active' => $data['is_active'] ?? true,
        ]);
    }
}