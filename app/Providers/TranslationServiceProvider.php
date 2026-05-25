<?php

namespace App\Providers;

use App\Services\Translation\DeepLTranslator;
use App\Services\Translation\OpenAITranslator;
use App\Services\Translation\TranslatorInterface;
use Illuminate\Support\ServiceProvider;

class TranslationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(TranslatorInterface::class, function ($app) {
            $provider = config('translation.provider', 'openai');

            return match ($provider) {
                'deepl' => new DeepLTranslator(),
                default => new OpenAITranslator(),
            };
        });
    }

    public function boot(): void
    {
        //
    }
}
