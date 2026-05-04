<?php

namespace App\Filament\Pages;

use App\Models\SiteSetting;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class ManageStripe extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static ?string $navigationLabel = 'Stripe';

    protected static ?string $navigationGroup = 'Contenu';

    protected static ?int $navigationSort = 5;

    protected static string $view = 'filament.pages.manage-stripe';

    public ?array $data = [];

    public function mount(): void
    {
        $stripeSettings = SiteSetting::where('key', 'stripe')->first();
        $this->data = $stripeSettings?->value ?? [];
    }

    public function save(): void
    {
        $this->validate([
            'data.publishable_key' => 'nullable|string',
            'data.secret_key' => 'nullable|string',
            'data.webhook_secret' => 'nullable|string',
            'data.mode' => 'nullable|in:test,live',
        ]);

        SiteSetting::updateOrCreate(
            ['key' => 'stripe'],
            ['value' => $this->data]
        );

        // Also update .env values for config access
        $this->updateEnv();

        Notification::make()
            ->title('Paramètres Stripe enregistrés')
            ->success()
            ->send();
    }

    protected function updateEnv(): void
    {
        $envPath = base_path('.env');
        if (!file_exists($envPath)) return;

        $env = file_get_contents($envPath);

        $env = preg_replace(
            '/^STRIPE_KEY=.*/m',
            'STRIPE_KEY=' . ($this->data['publishable_key'] ?? ''),
            $env
        ) ?? $env;

        $env = preg_replace(
            '/^STRIPE_SECRET=.*/m',
            'STRIPE_SECRET=' . ($this->data['secret_key'] ?? ''),
            $env
        ) ?? $env;

        $env = preg_replace(
            '/^STRIPE_WEBHOOK_SECRET=.*/m',
            'STRIPE_WEBHOOK_SECRET=' . ($this->data['webhook_secret'] ?? ''),
            $env
        ) ?? $env;

        file_put_contents($envPath, $env);
    }
}
