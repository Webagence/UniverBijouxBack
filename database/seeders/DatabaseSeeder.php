<?php

namespace Database\Seeders;

use App\Models\ContentBlock;
use App\Models\FaqItem;
use App\Models\Role;
use App\Models\SiteSetting;
use App\Models\Testimonial;
use App\Models\Universe;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            UserSeeder::class,
            UniverseSeeder::class,
            ProductSeeder::class,
            ContentSeeder::class,
        ]);
    }
}
