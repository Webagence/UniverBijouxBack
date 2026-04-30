<?php

namespace App\Filament\Pages;

use App\Models\ContentBlock;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Storage;
use Livewire\WithFileUploads;

class ManageContent extends Page
{
    use WithFileUploads;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Pages & Contenu';

    protected static ?string $navigationGroup = 'Contenu';

    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.manage-content';

    public ?array $heroData = [];

    public ?array $atelierData = [];

    public ?string $heroImage = null;

    public ?string $atelierImage = null;

    public $heroImageFile = null;

    public $atelierImageFile = null;

    public function mount(): void
    {
        $this->loadContent();
    }

    protected function loadContent(): void
    {
        $heroBlock = ContentBlock::where('key', 'hero')->first();
        $this->heroData = $heroBlock?->data ?? [];
        $this->heroImage = $this->heroData['image'] ?? null;

        $atelierBlock = ContentBlock::where('key', 'atelier')->first();
        $this->atelierData = $atelierBlock?->data ?? [];
        $this->atelierImage = $this->atelierData['image'] ?? null;
    }

    public function saveHero(): void
    {
        if ($this->heroImageFile) {
            $this->validate([
                'heroImageFile' => 'image|max:5120',
            ]);
            $path = $this->heroImageFile->store('content/hero', 'public');
            $this->heroImage = Storage::url($path);
            $this->heroData['image'] = $this->heroImage;
            $this->heroImageFile = null;
        }

        ContentBlock::updateOrCreate(
            ['key' => 'hero'],
            ['data' => $this->heroData]
        );

        Notification::make()
            ->title('Section Hero enregistrée')
            ->success()
            ->send();
    }

    public function saveAtelier(): void
    {
        if ($this->atelierImageFile) {
            $this->validate([
                'atelierImageFile' => 'image|max:5120',
            ]);
            $path = $this->atelierImageFile->store('content/atelier', 'public');
            $this->atelierImage = Storage::url($path);
            $this->atelierData['image'] = $this->atelierImage;
            $this->atelierImageFile = null;
        }

        ContentBlock::updateOrCreate(
            ['key' => 'atelier'],
            ['data' => $this->atelierData]
        );

        Notification::make()
            ->title('Section Atelier enregistrée')
            ->success()
            ->send();
    }

    public function removeHeroImage(): void
    {
        $this->heroImage = null;
        unset($this->heroData['image']);
    }

    public function removeAtelierImage(): void
    {
        $this->atelierImage = null;
        unset($this->atelierData['image']);
    }
}
