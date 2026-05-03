<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Universe;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $colliers = Universe::where('slug', 'colliers')->first();
        $boucles = Universe::where('slug', 'boucles')->first();
        $bagues = Universe::where('slug', 'bagues')->first();
        $bracelets = Universe::where('slug', 'bracelets')->first();

        $products = [
            ['name' => 'Collier Solène', 'universe_id' => $colliers->id, 'price_ht' => 18, 'tag' => 'Nouveauté', 'is_new' => true, 'stock' => 120],
            ['name' => 'Collier Céleste', 'universe_id' => $colliers->id, 'price_ht' => 22, 'tag' => 'Nouveauté', 'is_new' => true, 'stock' => 85],
            ['name' => 'Sautoir Héloïse', 'universe_id' => $colliers->id, 'price_ht' => 26, 'tag' => 'Nouveauté', 'is_new' => true, 'stock' => 60],
            ['name' => 'Ras-de-cou Lila', 'universe_id' => $colliers->id, 'price_ht' => 19, 'tag' => 'Nouveauté', 'is_new' => true, 'stock' => 95],
            ['name' => 'Collier Margot', 'universe_id' => $colliers->id, 'price_ht' => 21, 'tag' => 'Best-seller', 'is_new' => false, 'stock' => 150],
            ['name' => 'Collier Inès', 'universe_id' => $colliers->id, 'price_ht' => 24, 'tag' => null, 'is_new' => false, 'stock' => 75],

            ['name' => 'Créoles Aurore', 'universe_id' => $boucles->id, 'price_ht' => 14, 'tag' => 'Nouveauté', 'is_new' => true, 'stock' => 200],
            ['name' => 'Puces Étoile', 'universe_id' => $boucles->id, 'price_ht' => 11, 'tag' => 'Nouveauté', 'is_new' => true, 'stock' => 180],
            ['name' => 'Pendantes Mila', 'universe_id' => $boucles->id, 'price_ht' => 17, 'tag' => 'Nouveauté', 'is_new' => true, 'stock' => 90],
            ['name' => 'Créoles Jo', 'universe_id' => $boucles->id, 'price_ht' => 13, 'tag' => 'Nouveauté', 'is_new' => true, 'stock' => 110],
            ['name' => 'Boucles Noor', 'universe_id' => $boucles->id, 'price_ht' => 15, 'tag' => 'Best-seller', 'is_new' => false, 'stock' => 160],
            ['name' => 'Pendantes Rêve', 'universe_id' => $boucles->id, 'price_ht' => 18, 'tag' => 'Édition limitée', 'is_new' => false, 'stock' => 30],

            ['name' => 'Trio Étoile', 'universe_id' => $bagues->id, 'price_ht' => 20, 'tag' => 'Nouveauté', 'is_new' => true, 'stock' => 100],
            ['name' => 'Bague Signet Léa', 'universe_id' => $bagues->id, 'price_ht' => 23, 'tag' => 'Nouveauté', 'is_new' => true, 'stock' => 80],
            ['name' => 'Bague Jonc Or', 'universe_id' => $bagues->id, 'price_ht' => 17, 'tag' => 'Nouveauté', 'is_new' => true, 'stock' => 120],
            ['name' => 'Bague Pierre Iris', 'universe_id' => $bagues->id, 'price_ht' => 25, 'tag' => 'Nouveauté', 'is_new' => true, 'stock' => 45],
            ['name' => 'Bague Ondine', 'universe_id' => $bagues->id, 'price_ht' => 19, 'tag' => 'Best-seller', 'is_new' => false, 'stock' => 140],
            ['name' => 'Bague Soleil', 'universe_id' => $bagues->id, 'price_ht' => 21, 'tag' => null, 'is_new' => false, 'stock' => 90],

            ['name' => 'Bracelet Perle', 'universe_id' => $bracelets->id, 'price_ht' => 16, 'tag' => 'Nouveauté', 'is_new' => true, 'stock' => 130],
            ['name' => 'Jonc Clara', 'universe_id' => $bracelets->id, 'price_ht' => 19, 'tag' => 'Nouveauté', 'is_new' => true, 'stock' => 95],
            ['name' => 'Chaîne Romy', 'universe_id' => $bracelets->id, 'price_ht' => 17, 'tag' => 'Nouveauté', 'is_new' => true, 'stock' => 110],
            ['name' => 'Gourmette Juno', 'universe_id' => $bracelets->id, 'price_ht' => 22, 'tag' => 'Nouveauté', 'is_new' => true, 'stock' => 70],
            ['name' => 'Bracelet Sorel', 'universe_id' => $bracelets->id, 'price_ht' => 18, 'tag' => 'Best-seller', 'is_new' => false, 'stock' => 155],
            ['name' => 'Bracelet Lune', 'universe_id' => $bracelets->id, 'price_ht' => 23, 'tag' => 'Édition limitée', 'is_new' => false, 'stock' => 25],
        ];

        foreach ($products as $data) {
            $universeSlug = Universe::find($data['universe_id'])?->slug ?? 'colliers';
            Product::create([
                'name' => $data['name'],
                'universe_id' => $data['universe_id'],
                'price_ht' => $data['price_ht'],
                'retail_ttc' => round($data['price_ht'] * 2.8 * 1.2, 2),
                'vat_rate' => 20,
                'moq' => 3,
                'pack_size' => 3,
                'stock' => $data['stock'],
                'images' => ["images/products/{$universeSlug}.jpg"],
                'material' => 'Laiton doré à l\'or fin 3 microns',
                'finish' => 'Finition mate & brillante',
                'description' => 'Pièce signature fabriquée à la main dans notre atelier parisien. Or recyclé, pierres éthiques, finitions soignées. Livré en écrin Maison Lune.',
                'tag' => $data['tag'],
                'is_new' => $data['is_new'],
                'active' => true,
            ]);
        }
    }
}
