<?php

namespace App\Filament\Pages;

use App\Models\TranslationBatch;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;

class ManageTranslations extends Page implements Tables\Contracts\HasTable
{
    use Tables\Concerns\InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-language';

    protected static ?string $navigationLabel = 'Traductions';

    protected static ?string $navigationGroup = 'Contenu';

    protected static ?int $navigationSort = 5;

    protected static string $view = 'filament.pages.manage-translations';

    public string $provider = 'deepl';

    public string $deeplApiKey = '';

    public string $openaiApiKey = '';

    public string $openaiModel = 'gpt-4o-mini';

    public bool $autoTranslate = true;

    public int $cacheTtl = 86400;

    public function mount(): void
    {
        $this->provider = config('translation.provider', 'deepl');
        $this->deeplApiKey = config('translation.deepl.api_key', '');
        $this->openaiApiKey = config('translation.openai.api_key', '');
        $this->openaiModel = config('translation.openai.model', 'gpt-4o-mini');
        $this->autoTranslate = config('translation.auto_translate', true);
        $this->cacheTtl = config('translation.cache_ttl', 86400);
    }

    public function saveSettings(): void
    {
        $envPath = base_path('.env');
        $env = file_get_contents($envPath);

        $replacements = [
            '/TRANSLATION_PROVIDER=.*/' => "TRANSLATION_PROVIDER={$this->provider}",
            '/DEEPL_API_KEY=.*/' => "DEEPL_API_KEY={$this->deeplApiKey}",
            '/OPENAI_API_KEY=.*/' => "OPENAI_API_KEY={$this->openaiApiKey}",
            '/OPENAI_TRANSLATION_MODEL=.*/' => "OPENAI_TRANSLATION_MODEL={$this->openaiModel}",
            '/TRANSLATION_AUTO_TRANSLATE=.*/' => 'TRANSLATION_AUTO_TRANSLATE=' . ($this->autoTranslate ? 'true' : 'false'),
            '/TRANSLATION_CACHE_TTL=.*/' => "TRANSLATION_CACHE_TTL={$this->cacheTtl}",
        ];

        foreach ($replacements as $pattern => $replacement) {
            $env = preg_replace($pattern, $replacement, $env);
        }

        file_put_contents($envPath, $env);

        Artisan::call('config:clear');
        Artisan::call('cache:clear');

        Notification::make()
            ->title('Configuration de traduction sauvegardée')
            ->success()
            ->send();
    }

    public function translateAllProducts(): void
    {
        Artisan::call('translate:existing', [
            '--model' => 'Product',
            '--target' => 'en',
            '--source' => 'fr',
        ]);

        Notification::make()
            ->title('Traduction des produits lancée')
            ->body('Les jobs de traduction ont été dispatchés.')
            ->success()
            ->send();
    }

    public function translateAllUniverses(): void
    {
        Artisan::call('translate:existing', [
            '--model' => 'Universe',
            '--target' => 'en',
            '--source' => 'fr',
        ]);

        Notification::make()
            ->title('Traduction des univers lancée')
            ->body('Les jobs de traduction ont été dispatchés.')
            ->success()
            ->send();
    }

    public function translateAllContent(): void
    {
        Artisan::call('translate:existing', [
            '--all' => true,
            '--target' => 'en',
            '--source' => 'fr',
        ]);

        Notification::make()
            ->title('Traduction de tout le contenu lancée')
            ->body('Les jobs de traduction ont été dispatchés.')
            ->success()
            ->send();
    }

    public function clearTranslationCache(): void
    {
        Cache::tags(['translations'])->flush();

        Notification::make()
            ->title('Cache de traduction vidé')
            ->success()
            ->send();
    }

    public function getTableQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return TranslationBatch::query()->latest();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(TranslationBatch::query()->latest())
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->limit(40),
                Tables\Columns\TextColumn::make('source_locale')
                    ->badge(),
                Tables\Columns\TextColumn::make('target_locale')
                    ->badge(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'completed' => 'success',
                        'processing' => 'warning',
                        'failed' => 'danger',
                        'cancelled' => 'gray',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('total_items')
                    ->label('Total'),
                Tables\Columns\TextColumn::make('completed_items')
                    ->label('Terminés'),
                Tables\Columns\TextColumn::make('failed_items')
                    ->label('Échoués'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('d/m/Y H:i'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->emptyStateHeading('Aucun batch de traduction');
    }
}
