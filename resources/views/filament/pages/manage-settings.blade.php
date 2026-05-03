<x-filament-panels::page>
    <x-filament::section heading="Paramètres généraux du site">
        <div class="mb-6">
            <h3 class="text-lg font-medium mb-4 dark:text-gray-200">Identité visuelle</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
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
                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Affiché si aucun logo n'est uploadé.</p>
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
                            <img src="{{ $logoUrl }}" alt="Logo" class="h-16 w-auto object-contain border rounded bg-gray-50 dark:bg-gray-800 p-2" />
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
                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">PNG, JPG ou SVG. Max 5 MB. Prioritaire sur le texte.</p>
            </div>
        </div>

        <div class="mt-6">
            <h3 class="text-lg font-medium mb-4 dark:text-gray-200">Coordonnées</h3>
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
            <h3 class="text-lg font-medium mb-4 dark:text-gray-200">Livraison</h3>
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
            <h3 class="text-lg font-medium mb-4 dark:text-gray-200">Réseaux sociaux</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <x-filament::input.wrapper label="Instagram (URL)">
                    <x-filament::input
                        type="url"
                        wire:model="data.socialInstagram"
                        placeholder="https://instagram.com/maisonlune"
                    />
                </x-filament::input.wrapper>

                <x-filament::input.wrapper label="Facebook (URL)">
                    <x-filament::input
                        type="url"
                        wire:model="data.socialFacebook"
                        placeholder="https://facebook.com/maisonlune"
                    />
                </x-filament::input.wrapper>

                <x-filament::input.wrapper label="LinkedIn (URL)">
                    <x-filament::input
                        type="url"
                        wire:model="data.socialLinkedin"
                        placeholder="https://linkedin.com/company/maisonlune"
                    />
                </x-filament::input.wrapper>
            </div>
        </div>

        <div class="mt-6">
            <h3 class="text-lg font-medium mb-4 dark:text-gray-200">Pied de page (Footer)</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <x-filament::input.wrapper label="Texte de copyright">
                    <x-filament::input
                        type="text"
                        wire:model="data.copyright"
                        placeholder="© 2026 UNIVER BIJOUX · Grossiste B2B"
                    />
                </x-filament::input.wrapper>

                <x-filament::input.wrapper label="SIRET">
                    <x-filament::input
                        type="text"
                        wire:model="data.siret"
                        placeholder="123 456 789 00012"
                    />
                </x-filament::input.wrapper>
            </div>
        </div>

        <div class="mt-6">
            <h3 class="text-lg font-medium mb-4 dark:text-gray-200">Annonces (bandeau)</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">
                Chaque ligne est une annonce affichée en bandeau sur le site. Ajoutez ou supprimez des annonces à volonté.
            </p>

            <div class="space-y-3">
                @foreach($announcements as $index => $announcement)
                    <div class="flex items-center gap-3">
                        <span class="text-sm text-gray-400 dark:text-gray-500 w-6 text-right">{{ $index + 1 }}</span>
                        <x-filament::input
                            type="text"
                            wire:model="announcements.{{ $index }}"
                            placeholder="Ex : Réservé aux professionnels"
                            class="flex-1"
                        />
                        @if(count($announcements) > 1)
                            <x-filament::button wire:click="removeAnnouncement({{ $index }})" color="danger" size="sm" outlined>
                                <x-heroicon-m-trash class="w-4 h-4" />
                            </x-filament::button>
                        @endif
                    </div>
                @endforeach
            </div>

            <div class="mt-3">
                <x-filament::button wire:click="addAnnouncement" color="gray" size="sm" outlined>
                    <x-heroicon-m-plus class="w-4 h-4 mr-1" />
                    Ajouter une annonce
                </x-filament::button>
            </div>
        </div>

        <div class="mt-6 flex justify-end">
            <x-filament::button wire:click="save" color="primary">
                Enregistrer les paramètres
            </x-filament::button>
        </div>
    </x-filament::section>
</x-filament-panels::page>
