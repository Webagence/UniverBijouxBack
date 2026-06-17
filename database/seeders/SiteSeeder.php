<?php

namespace Database\Seeders;

use App\Models\Site;
use Illuminate\Database\Seeder;

class SiteSeeder extends Seeder
{
    public function run(): void
    {
        Site::create([
            'slug' => 'bijoux',
            'name' => 'Bijoux',
            'domain' => 'bijoux.francegems.com',
            'description' => 'Boutique de bijoux de luxe et artisanaux',
            'is_active' => true,
        ]);

        Site::create([
            'slug' => 'pierres',
            'name' => 'Pierres Précieuses',
            'domain' => 'pierres.francegems.com',
            'description' => 'Boutique de pierres précieuses et semi-précieuses',
            'is_active' => true,
        ]);
    }
}
