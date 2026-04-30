<?php

namespace Database\Seeders;

use App\Models\Universe;
use Illuminate\Database\Seeder;

class UniverseSeeder extends Seeder
{
    public function run(): void
    {
        Universe::create([
            'slug' => 'colliers',
            'name' => 'Colliers',
            'description' => 'Collection de colliers délicats fabriqués à la main',
            'image_url' => 'storage/images/products/colliers.jpg',
            'display_order' => 1,
        ]);

        Universe::create([
            'slug' => 'boucles',
            'name' => 'Boucles d\'oreilles',
            'description' => 'Boucles d\'oreilles élégantes pour toutes les occasions',
            'image_url' => 'storage/images/products/boucles.jpg',
            'display_order' => 2,
        ]);

        Universe::create([
            'slug' => 'bagues',
            'name' => 'Bagues',
            'description' => 'Bagues signature en or recyclé et pierres éthiques',
            'image_url' => 'storage/images/products/bagues.jpg',
            'display_order' => 3,
        ]);

        Universe::create([
            'slug' => 'bracelets',
            'name' => 'Bracelets',
            'description' => 'Bracelets raffinés pour sublimer le poignet',
            'image_url' => 'storage/images/products/bracelets.jpg',
            'display_order' => 4,
        ]);
    }
}
