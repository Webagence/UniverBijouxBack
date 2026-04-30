<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        Role::create(['name' => 'admin', 'label' => 'Administrateur']);
        Role::create(['name' => 'pro', 'label' => 'Client professionnel']);
    }
}
