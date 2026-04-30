<x-filament-panels::page>
    {{-- Hero Section --}}
    <x-filament::section heading="Section Hero (Page d'accueil)" description="Contenu de la bannière principale">
        {{-- Hero Image Upload --}}
        <div class="mb-6">
            <label class="block text-sm font-medium mb-2">Image de fond</label>
            @if($heroImage)
                <div class="relative inline-block mb-3">
                    <img src="{{ $heroImage }}" class="h-40 object-cover rounded-lg border">
                    <button
                        type="button"
                        wire:click="removeHeroImage"
                        class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs hover:bg-red-600"
                    >×</button>
                </div>
            @endif
            <input
                type="file"
                wire:model="heroImageFile"
                accept="image/jpeg,image/png,image/webp"
                class="block w-full text-sm text-gray-500
                    file:mr-4 file:py-2 file:px-4
                    file:rounded-lg file:border-0
                    file:text-sm file:font-semibold
                    file:bg-primary-50 file:text-primary-700
                    hover:file:bg-primary-100"
            />
            <p class="text-xs text-gray-500 mt-1">JPEG, PNG ou WebP (max 5MB)</p>
            @error('heroImageFile') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <x-filament::input.wrapper label="Surtitre (Eyebrow)">
                <x-filament::input
                    type="text"
                    wire:model="heroData.eyebrow"
                    placeholder="Grossiste bijoux · Collection Printemps 2026"
                />
            </x-filament::input.wrapper>

            <x-filament::input.wrapper label="Titre - Partie 1">
                <x-filament::input
                    type="text"
                    wire:model="heroData.titleLine1"
                    placeholder="Le bijou"
                />
            </x-filament::input.wrapper>

            <x-filament::input.wrapper label="Titre - Mise en avant (or)">
                <x-filament::input
                    type="text"
                    wire:model="heroData.titleEm"
                    placeholder="français"
                />
            </x-filament::input.wrapper>

            <x-filament::input.wrapper label="Titre - Partie 2">
                <x-filament::input
                    type="text"
                    wire:model="heroData.titleLine2"
                    placeholder="pour les pros"
                />
            </x-filament::input.wrapper>
        </div>

        <div class="mt-6">
            <x-filament::input.wrapper label="Paragraphe">
                <x-filament::input
                    type="textarea"
                    wire:model="heroData.paragraph"
                    rows="3"
                />
            </x-filament::input.wrapper>
        </div>

        <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
            <x-filament::input.wrapper label="Texte CTA Principal">
                <x-filament::input
                    type="text"
                    wire:model="heroData.ctaPrimary"
                    placeholder="Voir le catalogue"
                />
            </x-filament::input.wrapper>

            <x-filament::input.wrapper label="Texte CTA Secondaire">
                <x-filament::input
                    type="text"
                    wire:model="heroData.ctaSecondary"
                    placeholder="Ouvrir un compte pro"
                />
            </x-filament::input.wrapper>
        </div>

        <div class="mt-6">
            <h3 class="text-lg font-medium mb-4">Statistiques</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <x-filament::input.wrapper label="Stat 1 - Valeur">
                    <x-filament::input
                        type="text"
                        wire:model="heroData.stat1Value"
                        placeholder="850+"
                    />
                </x-filament::input.wrapper>

                <x-filament::input.wrapper label="Stat 1 - Label">
                    <x-filament::input
                        type="text"
                        wire:model="heroData.stat1Label"
                        placeholder="revendeurs partenaires"
                    />
                </x-filament::input.wrapper>

                <x-filament::input.wrapper label="Stat 2 - Valeur">
                    <x-filament::input
                        type="text"
                        wire:model="heroData.stat2Value"
                        placeholder="48h"
                    />
                </x-filament::input.wrapper>

                <x-filament::input.wrapper label="Stat 2 - Label">
                    <x-filament::input
                        type="text"
                        wire:model="heroData.stat2Label"
                        placeholder="délai de livraison"
                    />
                </x-filament::input.wrapper>
            </div>
        </div>

        <div class="mt-6">
            <x-filament::input.wrapper label="Citation / Quote">
                <x-filament::input
                    type="textarea"
                    wire:model="heroData.quote"
                    rows="2"
                    placeholder="Un bijou est un sourire qui ne s'efface jamais."
                />
            </x-filament::input.wrapper>
        </div>

        <div class="mt-6 flex justify-end">
            <x-filament::button wire:click="saveHero" color="primary">
                Enregistrer la section Hero
            </x-filament::button>
        </div>
    </x-filament::section>

    {{-- Atelier Section --}}
    <x-filament::section heading="Section Atelier" description="Contenu de la section savoir-faire / atelier" class="mt-8">
        {{-- Atelier Image Upload --}}
        <div class="mb-6">
            <label class="block text-sm font-medium mb-2">Image de l'atelier</label>
            @if($atelierImage)
                <div class="relative inline-block mb-3">
                    <img src="{{ $atelierImage }}" class="h-40 object-cover rounded-lg border">
                    <button
                        type="button"
                        wire:click="removeAtelierImage"
                        class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs hover:bg-red-600"
                    >×</button>
                </div>
            @endif
            <input
                type="file"
                wire:model="atelierImageFile"
                accept="image/jpeg,image/png,image/webp"
                class="block w-full text-sm text-gray-500
                    file:mr-4 file:py-2 file:px-4
                    file:rounded-lg file:border-0
                    file:text-sm file:font-semibold
                    file:bg-primary-50 file:text-primary-700
                    hover:file:bg-primary-100"
            />
            <p class="text-xs text-gray-500 mt-1">JPEG, PNG ou WebP (max 5MB)</p>
            @error('atelierImageFile') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <x-filament::input.wrapper label="Surtitre (Eyebrow)">
                <x-filament::input
                    type="text"
                    wire:model="atelierData.eyebrow"
                    placeholder="Notre atelier"
                />
            </x-filament::input.wrapper>

            <x-filament::input.wrapper label="Titre - Partie 1">
                <x-filament::input
                    type="text"
                    wire:model="atelierData.title"
                    placeholder="Le savoir-faire,"
                />
            </x-filament::input.wrapper>

            <x-filament::input.wrapper label="Titre - Mise en avant (or)">
                <x-filament::input
                    type="text"
                    wire:model="atelierData.titleEm"
                    placeholder="geste après geste"
                />
            </x-filament::input.wrapper>
        </div>

        <div class="mt-6">
            <x-filament::input.wrapper label="Paragraphe 1">
                <x-filament::input
                    type="textarea"
                    wire:model="atelierData.paragraph1"
                    rows="3"
                />
            </x-filament::input.wrapper>
        </div>

        <div class="mt-6">
            <x-filament::input.wrapper label="Paragraphe 2">
                <x-filament::input
                    type="textarea"
                    wire:model="atelierData.paragraph2"
                    rows="3"
                />
            </x-filament::input.wrapper>
        </div>

        <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
            <x-filament::input.wrapper label="Badge - Nombre">
                <x-filament::input
                    type="text"
                    wire:model="atelierData.badgeNumber"
                    placeholder="12"
                />
            </x-filament::input.wrapper>

            <x-filament::input.wrapper label="Badge - Label">
                <x-filament::input
                    type="text"
                    wire:model="atelierData.badgeLabel"
                    placeholder="Artisans dans notre atelier parisien"
                />
            </x-filament::input.wrapper>
        </div>

        <div class="mt-6 flex justify-end">
            <x-filament::button wire:click="saveAtelier" color="primary">
                Enregistrer la section Atelier
            </x-filament::button>
        </div>
    </x-filament::section>
</x-filament-panels::page>
