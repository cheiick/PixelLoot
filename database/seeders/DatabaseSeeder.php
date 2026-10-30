<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Product;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 3 catégories
        $categories = [
            ['name' => 'Électronique', 'slug' => 'electronique', 'description' => 'Tout ce qui touche à la technologie.'],
            ['name' => 'Vêtements', 'slug' => 'vetements', 'description' => 'Mode et accessoires.'],
            ['name' => 'Maison & Jardin', 'slug' => 'maison-jardin', 'description' => 'Décoration et jardinage.'],
        ];

        foreach ($categories as $catData) {
            $category = Category::create($catData);
            // 3 produits par catégorie pour un total de 9, puis un produit supplémentaire
            for ($i = 1; $i <= 3; $i++) {
                Product::create([
                    'category_id' => $category->id,
                    'name' => "Produit {$catData['name']} #{$i}",
                    'slug' => strtolower("produit-{$catData['slug']}-{$i}"),
                    'description' => "Description détaillée du produit {$catData['name']} numéro {$i}.", 
                    'price' => rand(10, 500) + round(rand() / getrandmax(), 2),
                    'stock' => rand(0, 20),
                    'image_url' => "https://picsum.photos/seed/{$catData['slug']}-{$i}/400/300",
                    'is_active' => true,
                ]);
            }
        }

        // Un produit supplémentaire pour atteindre 10
        $category = Category::inRandomOrder()->first();
        Product::create([
            'category_id' => $category->id,
            'name' => "Produit spécial {$category->name}",
            'slug' => 'produit-special',
            'description' => 'Un produit unique pour compléter le catalogue.',
            'price' => 199.99,
            'stock' => 5,
            'image_url' => "https://picsum.photos/seed/special/400/300",
            'is_active' => true,
        ]);
    }
}
