<x-filament-panels::page>
    <x-filament::section heading="Paramètres généraux du site">
        <div class="mb-6">
            <h3 class="text-lg font-medium mb-4">Identité visuelle</h3>
            <p class="text-sm text-gray-500 mb-4">
                Choisissez d'afficher un logo image ou le nom du site en texte. Si les deux sont définis, le logo est prioritaire.
            </p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <x-filament::input.wrapper label="Nom du site (texte)">
                        <x-filament::input
                            type="text"
                            wire:model="data.siteName"
                            placeholder="MAISON LUNE"
                        />
                    </x-filament::input.wrapper>
                    <p class="text-xs text-gray-400 mt-1">Affiché si aucun logo n'est uploadé.</p>
                </div>

                <div>
                    <x-filament::input.wrapper label="Slogan / Tagline">
                        <x-filament::input
                            type="text"
                            wire:model="data.tagline"
                            placeholder="Grossiste bijoux français"
                        />
                    </x-filament::input.wrapper>
                </div>
            </div>

            <div class="mt-6">
                <x-filament::input.wrapper label="Logo du site (image)">
                    @if($logoUrl)
                        <div class="flex items-center gap-4 mt-2">
                            <img src="{{ $logoUrl }}" alt="Logo" class="h-16 w-auto object-contain border rounded bg-gray-50 p-2" />
                            <x-filament::button wire:click="removeLogo" color="danger" size="sm">
                                Supprimer le logo
                            </x-filament::button>
                        </div>
                    @endif
                </x-filament::input.wrapper>
                <x-filament::input
                    type="file"
                    wire:model="logoFile"
                    accept="image/*"
                    class="mt-2"
                />
                @error('logoFile')
                    <p class="text-sm text-danger-600 mt-1">{{ $message }}</p>
                @enderror
                <p class="text-xs text-gray-400 mt-1">PNG, JPG ou SVG. Max 5 MB. Prioritaire sur le texte.</p>
            </div>
        </div>

        <div class="mt-6">
            <h3 class="text-lg font-medium mb-4">Coordonnées</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <x-filament::input.wrapper label="Email de contact">
                    <x-filament::input
                        type="email"
                        wire:model="data.email"
                        placeholder="pro@maisonlune.fr"
                    />
                </x-filament::input.wrapper>

                <x-filament::input.wrapper label="Téléphone">
                    <x-filament::input
                        type="text"
                        wire:model="data.phone"
                        placeholder="+33 1 42 00 00 00"
                    />
                </x-filament::input.wrapper>
            </div>

            <div class="mt-6">
                <x-filament::input.wrapper label="Adresse">
                    <x-filament::input
                        type="text"
                        wire:model="data.address"
                        placeholder="12 rue Saint-Honoré, 75001 Paris"
                    />
                </x-filament::input.wrapper>
            </div>
        </div>

        <div class="mt-6">
            <h3 class="text-lg font-medium mb-4">Livraison</h3>
            <x-filament::input.wrapper label="Franco de port à partir de (€ HT)">
                <x-filament::input
                    type="number"
                    wire:model="data.freeShippingFrom"
                    placeholder="300"
                    min="0"
                    step="1"
                />
            </x-filament::input.wrapper>
            <p class="text-sm text-gray-500 mt-1">
                Livraison offerte pour les commandes supérieures à ce montant.
            </p>
        </div>

        <div class="mt-6">
            <h3 class="text-lg font-medium mb-4">Annonces (bandeau)</h3>
            <x-filament::input.wrapper label="Annonces (une par ligne)">
                <x-filament::input
                    type="textarea"
                    wire:model="announcementsText"
                    rows="6"
                    placeholder="Réservé aux professionnels&#10;Prix HT — TVA 20%&#10;Franco de port dès 300€ HT&#10;Fabrication française&#10;Tarifs dégressifs&#10;Livraison 48h"
                />
            </x-filament::input.wrapper>
            <p class="text-sm text-gray-500 mt-1">
                Chaque ligne correspond à une annonce affichée en bandeau sur le site.
            </p>
        </div>

        <div class="mt-6 flex justify-end">
            <x-filament::button wire:click="save" color="primary">
                Enregistrer les paramètres
            </x-filament::button>
        </div>
    </x-filament::section>
</x-filament-panels::page>
