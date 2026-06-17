<?php

namespace App\Filament\Pages;

use App\Models\ShippingboSetting;
use App\Services\ShippingboService;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class ManageShippingbo extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-truck';

    protected static ?string $navigationLabel = 'Shippingbo';

    protected static ?string $navigationGroup = 'Intégrations';

    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.manage-shippingbo';

    public ?array $data = [];

    public ?string $authorizationUrl = null;

    public array $syncStatus = [];

    public function mount(): void
    {
        $settings = ShippingboSetting::where('key', 'shippingbo')->first();
        $this->data = $settings?->value ?? [
            'client_id' => '',
            'client_secret' => '',
            'app_id' => '',
            'webhook_secret' => '',
        ];

        if (ShippingboSetting::isConnected()) {
            try {
                $service = app(ShippingboService::class);
                $this->syncStatus = $service->getSyncStatus();
            } catch (\Exception $e) {
                $this->syncStatus = ['error' => $e->getMessage()];
            }
        }
    }

    public function save(): void
    {
        $this->validate([
            'data.client_id' => 'required|string',
            'data.client_secret' => 'nullable|string',
            'data.app_id' => 'nullable|string',
            'data.webhook_secret' => 'nullable|string',
        ]);

        ShippingboSetting::set('shippingbo', $this->data);
        ShippingboSetting::set('client_id', $this->data['client_id']);
        ShippingboSetting::set('client_secret', $this->data['client_secret']);
        ShippingboSetting::set('app_id', $this->data['app_id']);
        ShippingboSetting::set('webhook_secret', $this->data['webhook_secret'] ?? '');

        Notification::make()
            ->title('Paramètres Shippingbo enregistrés')
            ->success()
            ->send();
    }

    public function connect(): void
    {
        $this->save();

        try {
            $service = app(ShippingboService::class);
            $this->authorizationUrl = $service->getAuthorizationUrl(url('/api/shippingbo/callback'), [
                'orders', 'products', 'addresses', 'shipments'
            ]);

            Notification::make()
                ->title('Redirection vers Shippingbo pour autorisation')
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Erreur de connexion')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function syncAllProducts(): void
    {
        try {
            $service = app(ShippingboService::class);
            $results = $service->syncAllProducts();

            Notification::make()
                ->title('Synchronisation terminée')
                ->body("{$results['success']} produits synchronisés, {$results['failed']} échecs")
                ->success()
                ->send();

            $this->mount();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Erreur de synchronisation')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}
