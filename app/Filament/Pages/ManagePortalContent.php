<?php

namespace App\Filament\Pages;

use App\Models\ContentBlock;
use App\Models\Site;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Storage;
use Livewire\WithFileUploads;

class ManagePortalContent extends Page
{
    use WithFileUploads;

    protected static ?string $navigationIcon = 'heroicon-o-globe-alt';

    protected static ?string $navigationLabel = 'Contenu du Portail';

    protected static ?string $navigationGroup = 'Portail';

    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.manage-portal-content';

    public ?array $data = [];

    public $heroImageFile = null;
    public $gemsImageFile = null;
    public $jewelryImageFile = null;

    public ?string $heroImageUrl = null;
    public ?string $gemsImageUrl = null;
    public ?string $jewelryImageUrl = null;

    public function mount(): void
    {
        $this->load();
    }

    protected function load(): void
    {
        $site = Site::where('slug', 'portail')->first();
        $block = ContentBlock::where('key', 'portal')->when($site, fn($q) => $q->where('site_id', $site->id))->first();
        $this->data = $block?->data ?? [];
        $this->heroImageUrl = $this->data['hero_image'] ?? null;
        $this->gemsImageUrl = $this->data['univ_gems_image'] ?? null;
        $this->jewelryImageUrl = $this->data['univ_jewelry_image'] ?? null;
    }

    public function save(): void
    {
        $this->validate([
            'heroImageFile' => 'nullable|image|max:10240',
            'gemsImageFile' => 'nullable|image|max:10240',
            'jewelryImageFile' => 'nullable|image|max:10240',
        ]);

        if ($this->heroImageFile) {
            $path = $this->heroImageFile->store('portal/hero', 'public');
            $this->heroImageUrl = Storage::url($path);
            $this->data['hero_image'] = $this->heroImageUrl;
            $this->heroImageFile = null;
        }

        if ($this->gemsImageFile) {
            $path = $this->gemsImageFile->store('portal/univers', 'public');
            $this->gemsImageUrl = Storage::url($path);
            $this->data['univ_gems_image'] = $this->gemsImageUrl;
            $this->gemsImageFile = null;
        }

        if ($this->jewelryImageFile) {
            $path = $this->jewelryImageFile->store('portal/univers', 'public');
            $this->jewelryImageUrl = Storage::url($path);
            $this->data['univ_jewelry_image'] = $this->jewelryImageUrl;
            $this->jewelryImageFile = null;
        }

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

    public function removeHeroImage(): void
    {
        $this->heroImageUrl = null;
        unset($this->data['hero_image']);
    }

    public function removeGemsImage(): void
    {
        $this->gemsImageUrl = null;
        unset($this->data['univ_gems_image']);
    }

    public function removeJewelryImage(): void
    {
        $this->jewelryImageUrl = null;
        unset($this->data['univ_jewelry_image']);
    }
}
