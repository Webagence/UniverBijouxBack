<?php

namespace App\Filament\Pages;

use App\Models\SiteSetting;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class ManageSettings extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationLabel = 'Paramètres';

    protected static ?string $navigationGroup = 'Contenu';

    protected static ?int $navigationSort = 4;

    protected static string $view = 'filament.pages.manage-settings';

    public ?array $data = [];

    public ?string $announcementsText = '';

    public function mount(): void
    {
        $this->loadSettings();
    }

    protected function loadSettings(): void
    {
        $settings = SiteSetting::where('key', 'general')->first();
        $this->data = $settings?->value ?? [];

        if (isset($this->data['announcements']) && is_array($this->data['announcements'])) {
            $this->announcementsText = implode("\n", $this->data['announcements']);
        }
    }

    public function save(): void
    {
        $this->validate([
            'data.siteName' => 'required|string|max:255',
            'data.email' => 'nullable|email',
            'data.phone' => 'nullable|string|max:20',
            'data.freeShippingFrom' => 'nullable|numeric|min:0',
        ]);

        if ($this->announcementsText) {
            $this->data['announcements'] = array_filter(
                array_map('trim', explode("\n", $this->announcementsText))
            );
        } else {
            $this->data['announcements'] = [];
        }

        SiteSetting::updateOrCreate(
            ['key' => 'general'],
            ['value' => $this->data]
        );

        Notification::make()
            ->title('Paramètres enregistrés')
            ->success()
            ->send();
    }
}
