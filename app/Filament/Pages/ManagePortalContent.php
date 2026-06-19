<?php

namespace App\Filament\Pages;

use App\Models\ContentBlock;
use App\Models\Site;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class ManagePortalContent extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-globe-alt';

    protected static ?string $navigationLabel = 'Contenu du Portail';

    protected static ?string $navigationGroup = 'Portail';

    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.manage-portal-content';

    public ?array $data = [];

    public function mount(): void
    {
        $this->load();
    }

    protected function load(): void
    {
        $site = Site::where('slug', 'portail')->first();
        $block = ContentBlock::where('key', 'portal')->when($site, fn($q) => $q->where('site_id', $site->id))->first();
        $this->data = $block?->data ?? [];
    }

    public function save(): void
    {
        $site = Site::where('slug', 'portail')->first();
        $attrs = ['key' => 'portal'];
        if ($site) {
            $attrs['site_id'] = $site->id;
        }

        ContentBlock::updateOrCreate($attrs, ['data' => $this->data]);

        Notification::make()
            ->title('Contenu du portail enregistré')
            ->success()
            ->send();
    }
}
