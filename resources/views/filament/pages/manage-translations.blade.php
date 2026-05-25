<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Configuration API --}}
        <x-filament::section>
            <x-slot name="heading">
                Configuration de l'API de traduction
            </x-slot>

            <form wire:submit="saveSettings" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-filament-forms::field-wrapper label="Provider">
                        <select wire:model="provider" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                            <option value="deepl">DeepL (recommandé)</option>
                            <option value="openai">OpenAI (GPT-4o-mini)</option>
                        </select>
                    </x-filament-forms::field-wrapper>

                    <x-filament-forms::field-wrapper label="Cache TTL (secondes)">
                        <input type="number" wire:model="cacheTtl" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500" />
                    </x-filament-forms::field-wrapper>
                </div>

                @if ($provider === 'deepl')
                    <x-filament-forms::field-wrapper label="Clé API DeepL">
                        <input type="password" wire:model="deeplApiKey" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500" placeholder="Votre clé API DeepL" />
                        <p class="text-xs text-gray-500 mt-1">
                            Obtenez votre clé sur <a href="https://www.deepl.com/pro-api" target="_blank" class="text-primary-600 underline">deepl.com/pro-api</a>
                        </p>
                    </x-filament-forms::field-wrapper>
                @endif

                @if ($provider === 'openai')
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <x-filament-forms::field-wrapper label="Clé API OpenAI">
                            <input type="password" wire:model="openaiApiKey" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500" placeholder="sk-proj-..." />
                        </x-filament-forms::field-wrapper>

                        <x-filament-forms::field-wrapper label="Modèle OpenAI">
                            <select wire:model="openaiModel" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                <option value="gpt-4o-mini">gpt-4o-mini (rapide, économique)</option>
                                <option value="gpt-4o">gpt-4o (meilleure qualité)</option>
                                <option value="gpt-4o-mini">gpt-4o-mini</option>
                            </select>
                        </x-filament-forms::field-wrapper>
                    </div>
                @endif

                <x-filament-forms::field-wrapper label="Traduction automatique">
                    <label class="flex items-center gap-2">
                        <input type="checkbox" wire:model="autoTranslate" class="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-500 focus:ring-primary-500" />
                        <span class="text-sm">Traduire automatiquement à la création/modification d'un contenu</span>
                    </label>
                </x-filament-forms::field-wrapper>

                <div class="flex justify-end">
                    <x-filament::button type="submit">
                        Sauvegarder la configuration
                    </x-filament::button>
                </div>
            </form>
        </x-filament::section>

        {{-- Actions de traduction --}}
        <x-filament::section>
            <x-slot name="heading">
                Traduire le contenu existant
            </x-slot>
            <x-slot name="description">
                Lance la traduction de tout le contenu français vers l'anglais. Les jobs sont traités en arrière-plan.
            </x-slot>

            <div class="flex flex-wrap gap-3">
                <x-filament::button wire:click="translateAllContent" color="primary">
                    Traduire tout le contenu
                </x-filament::button>

                <x-filament::button wire:click="translateAllProducts" color="info">
                    Traduire les produits
                </x-filament::button>

                <x-filament::button wire:click="translateAllUniverses" color="info">
                    Traduire les univers
                </x-filament::button>

                <x-filament::button wire:click="clearTranslationCache" color="gray">
                    Vider le cache
                </x-filament::button>
            </div>
        </x-filament::section>

        {{-- Historique des batches --}}
        <x-filament::section>
            <x-slot name="heading">
                Historique des traductions
            </x-slot>

            {{ $this->table }}
        </x-filament::section>
    </div>
</x-filament-panels::page>
