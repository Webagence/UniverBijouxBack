<x-filament-panels::page>
    {{-- Hero Section --}}
    <x-filament::section heading="Section Hero (Page d'accueil)" description="Contenu de la bannière principale">
        <x-filament-forms::file-upload
            wire:model="heroImage"
            label="Image de fond"
            image
            directory="content/hero"
            :image-preview-height="200"
            :max-width="400"
            accepted-file-types="['image/jpeg', 'image/png', 'image/webp']"
        />

        <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
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
        <x-filament-forms::file-upload
            wire:model="atelierImage"
            label="Image de l'atelier"
            image
            directory="content/atelier"
            :image-preview-height="200"
            :max-width="400"
            accepted-file-types="['image/jpeg', 'image/png', 'image/webp']"
        />

        <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
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
