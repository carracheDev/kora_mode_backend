<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Femme', 'slug' => 'femme', 'description' => 'Sélection mode femme.', 'sort_order' => 1],
            ['name' => 'Homme', 'slug' => 'homme', 'description' => 'Sélection mode homme.', 'sort_order' => 2],
            ['name' => 'Mixte', 'slug' => 'mixte', 'description' => 'Pièces à porter au quotidien.', 'sort_order' => 3],
            ['name' => 'Accessoires', 'slug' => 'accessoires', 'description' => 'Accessoires et chaussures.', 'sort_order' => 4],
        ];

        foreach ($categories as $category) {
            Category::query()->updateOrCreate(
                ['slug' => $category['slug']],
                [...$category, 'is_active' => true, 'is_demo_data' => true],
            );
        }
    }
}
