<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            [
                'name' => 'Blazer oversize Nova', 'slug' => 'blazer-oversize-nova', 'sku' => 'KM-NOVA-001', 'category' => 'femme',
                'gender' => 'femme', 'subcategory' => 'Vestes', 'price' => 38000, 'old_price' => null,
                'description' => 'Une coupe ample et structurée, pensée pour composer une silhouette contemporaine.',
                'images' => ['/Images/Produits/blazer-oversize-nova-1.webp', '/Images/Produits/blazer-oversize-nova-2.webp'],
                'sizes' => ['S', 'M', 'L', 'XL'], 'colors' => [['name' => 'Noir', 'hex' => '#171A1F'], ['name' => 'Sable', 'hex' => '#D9CCB6']],
                'stock' => 7, 'tags' => ['new', 'bestseller'], 'rating' => 4.8, 'popularity' => 96, 'is_featured' => true, 'created_at' => '2026-09-21 10:00:00',
            ],
            [
                'name' => 'Bomber Urban Ivoire', 'slug' => 'bomber-urban-ivoire', 'sku' => 'KM-URBAN-001', 'category' => 'mixte',
                'gender' => 'mixte', 'subcategory' => 'Vestes', 'price' => 32000, 'old_price' => 40000,
                'description' => 'Un bomber léger aux lignes nettes, facile à superposer au quotidien.',
                'images' => ['/Images/Produits/bomber-urban-ivoire-1.webp', '/Images/Produits/bomber-urban-ivoire-2.webp'],
                'sizes' => ['S', 'M', 'L', 'XL'], 'colors' => [['name' => 'Ivoire', 'hex' => '#F1ECE1'], ['name' => 'Olive', 'hex' => '#68745A']],
                'stock' => 12, 'tags' => ['promo', 'bestseller'], 'rating' => 4.7, 'popularity' => 100, 'is_featured' => true, 'created_at' => '2026-08-15 10:00:00',
            ],
            [
                'name' => 'Pantalon wide-leg Atlas', 'slug' => 'pantalon-wide-leg-atlas', 'sku' => 'KM-ATLAS-001', 'category' => 'femme',
                'gender' => 'femme', 'subcategory' => 'Pantalons', 'price' => 24000, 'old_price' => null,
                'description' => 'Une jambe ample et un tombé fluide pour une allure décontractée et précise.',
                'images' => ['/Images/Produits/pantalon-wide-leg-atlas-1.webp', '/Images/Produits/pantalon-wide-leg-atlas-2.webp'],
                'sizes' => ['XS', 'S', 'M', 'L'], 'colors' => [['name' => 'Noir', 'hex' => '#171A1F'], ['name' => 'Écru', 'hex' => '#E8E1D4']],
                'stock' => 9, 'tags' => ['new'], 'rating' => 4.6, 'popularity' => 83, 'is_featured' => false, 'created_at' => '2026-08-25 10:00:00',
            ],
            [
                'name' => 'Crop top Lune', 'slug' => 'crop-top-lune', 'sku' => 'KM-LUNE-001', 'category' => 'femme',
                'gender' => 'femme', 'subcategory' => 'Hauts', 'price' => 12000, 'old_price' => null,
                'description' => 'Un essentiel à la coupe courte, aux finitions sobres et faciles à associer.',
                'images' => ['/Images/Produits/crop-top-lune-1.webp', '/Images/Produits/crop-top-lune-2.webp'],
                'sizes' => ['XS', 'S', 'M', 'L'], 'colors' => [['name' => 'Blanc', 'hex' => '#F8F7F3'], ['name' => 'Noir', 'hex' => '#171A1F'], ['name' => 'Bordeaux', 'hex' => '#6D293C']],
                'stock' => 3, 'tags' => ['new'], 'rating' => 4.5, 'popularity' => 71, 'is_featured' => false, 'created_at' => '2026-09-26 10:00:00',
            ],
            [
                'name' => 'Jean relaxed Dakar', 'slug' => 'jean-relaxed-dakar', 'sku' => 'KM-DAKAR-001', 'category' => 'homme',
                'gender' => 'homme', 'subcategory' => 'Jeans', 'price' => 26000, 'old_price' => 32000,
                'description' => 'Un denim relaxed au volume équilibré, confortable du matin au soir.',
                'images' => ['/Images/Produits/jean-relaxed-dakar-1.webp', '/Images/Produits/jean-relaxed-dakar-2.webp'],
                'sizes' => ['S', 'M', 'L', 'XL', 'XXL'], 'colors' => [['name' => 'Bleu brut', 'hex' => '#314965'], ['name' => 'Délavé', 'hex' => '#74859B']],
                'stock' => 14, 'tags' => ['promo', 'bestseller'], 'rating' => 4.9, 'popularity' => 98, 'is_featured' => true, 'created_at' => '2026-08-12 10:00:00',
            ],
            [
                'name' => 'Veste utilitaire Zéro', 'slug' => 'veste-utilitaire-zero', 'sku' => 'KM-ZERO-001', 'category' => 'homme',
                'gender' => 'homme', 'subcategory' => 'Vestes', 'price' => 34000, 'old_price' => null,
                'description' => 'Une veste fonctionnelle aux détails discrets et à la silhouette moderne.',
                'images' => ['/Images/Produits/veste-utilitaire-zero-1.webp', '/Images/Produits/veste-utilitaire-zero-2.webp'],
                'sizes' => ['S', 'M', 'L', 'XL'], 'colors' => [['name' => 'Olive', 'hex' => '#68745A'], ['name' => 'Noir', 'hex' => '#171A1F']],
                'stock' => 6, 'tags' => ['new'], 'rating' => 4.4, 'popularity' => 77, 'is_featured' => false, 'created_at' => '2026-09-18 10:00:00',
            ],
            [
                'name' => 'Sac structuré Ayo', 'slug' => 'sac-structure-ayo', 'sku' => 'KM-AYO-001', 'category' => 'accessoires',
                'gender' => 'mixte', 'subcategory' => 'Sacs', 'price' => 29000, 'old_price' => 35000,
                'description' => 'Un format structuré et pratique, relevé par des lignes minimalistes.',
                'images' => ['/Images/Produits/sac-structure-ayo-1.webp', '/Images/Produits/sac-structure-ayo-2.webp'],
                'sizes' => ['Unique'], 'colors' => [['name' => 'Noir', 'hex' => '#171A1F'], ['name' => 'Cognac', 'hex' => '#A96C42']],
                'stock' => 4, 'tags' => ['promo', 'bestseller'], 'rating' => 4.8, 'popularity' => 92, 'is_featured' => true, 'created_at' => '2026-08-10 10:00:00',
            ],
            [
                'name' => 'Sneakers Blanc Studio', 'slug' => 'sneakers-blanc-studio', 'sku' => 'KM-STUDIO-001', 'category' => 'accessoires',
                'gender' => 'mixte', 'subcategory' => 'Chaussures', 'price' => 27000, 'old_price' => null,
                'description' => 'Des sneakers épurées pensées pour accompagner les silhouettes de tous les jours.',
                'images' => ['/Images/Produits/sneakers-blanc-studio-1.webp', '/Images/Produits/sneakers-blanc-studio-2.webp'],
                'sizes' => ['38', '39', '40', '41', '42', '43', '44'], 'colors' => [['name' => 'Blanc', 'hex' => '#F8F7F3'], ['name' => 'Blanc / Vert', 'hex' => '#0B8A5F']],
                'stock' => 8, 'tags' => ['new', 'bestseller'], 'rating' => 4.7, 'popularity' => 89, 'is_featured' => true, 'created_at' => '2026-08-14 10:00:00',
            ],
        ];

        foreach ($products as $product) {
            $category = Category::query()->where('slug', $product['category'])->firstOrFail();
            unset($product['category']);

            Product::query()->updateOrCreate(
                ['slug' => $product['slug']],
                [...$product, 'category_id' => $category->id, 'is_active' => true, 'is_demo_data' => true],
            );
        }
    }
}
