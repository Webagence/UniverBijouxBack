<?php

namespace App\Filament\Pages;

use App\Models\ContentBlock;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Storage;

class ManageContent extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Pages & Contenu';

    protected static ?string $navigationGroup = 'Contenu';

    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.manage-content';

    public ?array $heroData = [];

    public ?array $atelierData = [];

    public ?string $heroImage = null;

    public ?string $atelierImage = null;

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
        $this->validate([
            'heroData.titleLine1' => 'required|string',
            'heroData.titleEm' => 'required|string',
            'heroData.titleLine2' => 'required|string',
        ]);

        if ($this->heroImage) {
            $this->heroData['image'] = $this->heroImage;
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
        $this->validate([
            'atelierData.title' => 'required|string',
            'atelierData.titleEm' => 'required|string',
        ]);

        if ($this->atelierImage) {
            $this->atelierData['image'] = $this->atelierImage;
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

    public function handleHeroImageUpload($file): void
    {
        $path = $file->store('content/hero', 'public');
        $this->heroImage = Storage::url($path);
        $this->heroData['image'] = $this->heroImage;
    }

    public function handleAtelierImageUpload($file): void
    {
        $path = $file->store('content/atelier', 'public');
        $this->atelierImage = Storage::url($path);
        $this->atelierData['image'] = $this->atelierImage;
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
