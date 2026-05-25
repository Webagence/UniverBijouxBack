<?php

namespace Database\Seeders;

use App\Models\Locale;
use Illuminate\Database\Seeder;

class LocaleSeeder extends Seeder
{
    public function run(): void
    {
        $locales = [
            [
                'code' => 'fr',
                'name' => 'French',
                'native_name' => 'Français',
                'flag_emoji' => '🇫🇷',
                'is_active' => true,
                'is_default' => true,
                'direction' => 'ltr',
                'sort_order' => 1,
            ],
            [
                'code' => 'en',
                'name' => 'English',
                'native_name' => 'English',
                'flag_emoji' => '🇬🇧',
                'is_active' => true,
                'is_default' => false,
                'direction' => 'ltr',
                'sort_order' => 2,
            ],
        ];

        foreach ($locales as $localeData) {
            Locale::updateOrCreate(
                ['code' => $localeData['code']],
                $localeData
            );
        }
    }
}
