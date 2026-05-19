<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $user = User::query()->first();

        if (! $user) {
            return;
        }

        $categories = [
            [
                'name' => 'Streaming',
                'description' => 'Serviços como Netflix, Spotify e Prime Video.',
                'color' => '#EF4444',
            ],
            [
                'name' => 'Educação',
                'description' => 'Faculdade, cursos, plataformas e livros.',
                'color' => '#3B82F6',
            ],
            [
                'name' => 'Tecnologia',
                'description' => 'Hospedagem, domínios, servidores e SaaS.',
                'color' => '#8B5CF6',
            ],
            [
                'name' => 'Saúde',
                'description' => 'Academia, plano de saúde e bem-estar.',
                'color' => '#10B981',
            ],
        ];

        foreach ($categories as $category) {
            $user->categories()->firstOrCreate(
                ['name' => $category['name']],
                $category,
            );
        }
    }
}
