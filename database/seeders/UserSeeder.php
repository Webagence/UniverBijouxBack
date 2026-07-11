<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@univerbijoux.com',
            'password' => Hash::make('password123'),
            'phone' => '+33 1 42 00 00 00',
            'approved' => true,
        ]);
        $admin->assignRole('admin');

        $proUser = User::create([
            'name' => 'Boutique Écrin',
            'email' => 'contact@boutique-ecrin.fr',
            'password' => Hash::make('password123'),
            'phone' => '+33 5 56 00 00 00',
            'approved' => true,
        ]);
        $proUser->assignRole('pro');

        $pendingUser = User::create([
            'name' => 'Concept-store Ondine',
            'email' => 'contact@ondine-lyon.fr',
            'password' => Hash::make('password123'),
            'phone' => '+33 4 78 00 00 00',
            'approved' => false,
        ]);
        $pendingUser->assignRole('pro');
    }
}
