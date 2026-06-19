<x-filament-panels::page>
    <div class="space-y-8">

        {{-- Menu --}}
        <x-filament::section heading="Menu" description="Liens de navigation du portail" collapsible>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <x-filament::input.wrapper label="Univers">
                    <x-filament::input type="text" wire:model="data.nav_universe" placeholder="Univers" />
                </x-filament::input.wrapper>
                <x-filament::input.wrapper label="La Maison">
                    <x-filament::input type="text" wire:model="data.nav_house" placeholder="La Maison" />
                </x-filament::input.wrapper>
                <x-filament::input.wrapper label="Valeurs">
                    <x-filament::input type="text" wire:model="data.nav_values" placeholder="Valeurs" />
                </x-filament::input.wrapper>
                <x-filament::input.wrapper label="Contact">
                    <x-filament::input type="text" wire:model="data.nav_contact" placeholder="Contact" />
                </x-filament::input.wrapper>
            </div>
        </x-filament::section>

        {{-- Hero --}}
        <x-filament::section heading="Hero" description="Bannière principale du portail" collapsible>
            <div class="mb-6">
                <label class="block text-sm font-medium mb-2">Image de fond du hero</label>
                @if($heroImageUrl)
                    <div class="relative inline-block mb-3">
                        <img src="{{ $heroImageUrl }}" class="h-40 object-cover rounded-lg border w-auto">
                        <button type="button" wire:click="removeHeroImage" class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs hover:bg-red-600">×</button>
                    </div>
                @endif
                <input type="file" wire:model="heroImageFile" accept="image/jpeg,image/png,image/webp" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100">
                <p class="text-xs text-gray-500 mt-1">JPEG, PNG ou WebP (max 10MB). Ratio 16:9 recommandé.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <x-filament::input.wrapper label="Badge">
                    <x-filament::input type="text" wire:model="data.hero_badge" placeholder="Maison Française depuis 2008" />
                </x-filament::input.wrapper>
                <x-filament::input.wrapper label="Titre partie 1">
                    <x-filament::input type="text" wire:model="data.hero_title1" placeholder="Bienvenue chez" />
                </x-filament::input.wrapper>
                <x-filament::input.wrapper label="Titre partie 2 (or)">
                    <x-filament::input type="text" wire:model="data.hero_title2" placeholder="France Gems" />
                </x-filament::input.wrapper>
                <x-filament::input.wrapper label="Texte défilement">
                    <x-filament::input type="text" wire:model="data.hero_scroll" placeholder="Faites défiler" />
                </x-filament::input.wrapper>
                <div class="md:col-span-2">
                    <x-filament::input.wrapper label="Description">
                        <x-filament::input type="textarea" wire:model="data.hero_desc" rows="3" />
                    </x-filament::input.wrapper>
                </div>
                <x-filament::input.wrapper label="CTA 1 — Texte">
                    <x-filament::input type="text" wire:model="data.hero_cta1" placeholder="Explorer les Pierres" />
                </x-filament::input.wrapper>
                <x-filament::input.wrapper label="CTA 1 — URL">
                    <x-filament::input type="text" wire:model="data.hero_cta1_url" placeholder="https://pierres.francegems.com" />
                </x-filament::input.wrapper>
                <x-filament::input.wrapper label="CTA 2 — Texte">
                    <x-filament::input type="text" wire:model="data.hero_cta2" placeholder="Découvrir les Bijoux" />
                </x-filament::input.wrapper>
                <x-filament::input.wrapper label="CTA 2 — URL">
                    <x-filament::input type="text" wire:model="data.hero_cta2_url" placeholder="https://bijoux.francegems.com" />
                </x-filament::input.wrapper>
            </div>
        </x-filament::section>

        {{-- Section Univers --}}
        <x-filament::section heading="Section Univers" description="Deux cartes : Pierres & Bijoux" collapsible>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label class="block text-sm font-medium mb-2">Image — Carte Pierres</label>
                    @if($gemsImageUrl)
                        <div class="relative inline-block mb-3">
                            <img src="{{ $gemsImageUrl }}" class="h-32 object-cover rounded-lg border w-auto">
                            <button type="button" wire:click="removeGemsImage" class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs hover:bg-red-600">×</button>
                        </div>
                    @endif
                    <input type="file" wire:model="gemsImageFile" accept="image/jpeg,image/png,image/webp" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2">Image — Carte Bijoux</label>
                    @if($jewelryImageUrl)
                        <div class="relative inline-block mb-3">
                            <img src="{{ $jewelryImageUrl }}" class="h-32 object-cover rounded-lg border w-auto">
                            <button type="button" wire:click="removeJewelryImage" class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs hover:bg-red-600">×</button>
                        </div>
                    @endif
                    <input type="file" wire:model="jewelryImageFile" accept="image/jpeg,image/png,image/webp" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <x-filament::input.wrapper label="Surtitre section">
                    <x-filament::input type="text" wire:model="data.univ_kicker" placeholder="Deux Univers" />
                </x-filament::input.wrapper>
                <x-filament::input.wrapper label="Titre partie 1">
                    <x-filament::input type="text" wire:model="data.univ_title1" placeholder="Choisissez votre" />
                </x-filament::input.wrapper>
                <x-filament::input.wrapper label="Titre partie 2 (or)">
                    <x-filament::input type="text" wire:model="data.univ_title2" placeholder="passion" />
                </x-filament::input.wrapper>
                <x-filament::input.wrapper label="Badge Univers I">
                    <x-filament::input type="text" wire:model="data.univ_u1" placeholder="Univers I" />
                </x-filament::input.wrapper>
                <x-filament::input.wrapper label="Badge Univers II">
                    <x-filament::input type="text" wire:model="data.univ_u2" placeholder="Univers II" />
                </x-filament::input.wrapper>
                <x-filament::input.wrapper label="Titre Pierres">
                    <x-filament::input type="text" wire:model="data.univ_gems" placeholder="Pierres Précieuses" />
                </x-filament::input.wrapper>
                <x-filament::input.wrapper label="Titre Bijoux">
                    <x-filament::input type="text" wire:model="data.univ_jewelry" placeholder="Bijoux" />
                </x-filament::input.wrapper>
                <div class="md:col-span-2">
                    <x-filament::input.wrapper label="Description Pierres">
                        <x-filament::input type="textarea" wire:model="data.univ_gemsDesc" rows="2" />
                    </x-filament::input.wrapper>
                </div>
                <div class="md:col-span-2">
                    <x-filament::input.wrapper label="Description Bijoux">
                        <x-filament::input type="textarea" wire:model="data.univ_jewelryDesc" rows="2" />
                    </x-filament::input.wrapper>
                </div>
                <x-filament::input.wrapper label="CTA Pierres">
                    <x-filament::input type="text" wire:model="data.univ_gemsCta" placeholder="Accéder au catalogue des pierres" />
                </x-filament::input.wrapper>
                <x-filament::input.wrapper label="URL Pierres">
                    <x-filament::input type="text" wire:model="data.univ_gemsUrl" placeholder="https://pierres.francegems.com" />
                </x-filament::input.wrapper>
                <x-filament::input.wrapper label="CTA Bijoux">
                    <x-filament::input type="text" wire:model="data.univ_jewelryCta" placeholder="Accéder à la boutique bijoux" />
                </x-filament::input.wrapper>
                <x-filament::input.wrapper label="URL Bijoux">
                    <x-filament::input type="text" wire:model="data.univ_jewelryUrl" placeholder="https://bijoux.francegems.com" />
                </x-filament::input.wrapper>
            </div>
        </x-filament::section>

        {{-- Section La Maison --}}
        <x-filament::section heading="Section La Maison" description="Présentation et statistiques" collapsible>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <x-filament::input.wrapper label="Surtitre">
                    <x-filament::input type="text" wire:model="data.about_kicker" placeholder="La Maison" />
                </x-filament::input.wrapper>
                <x-filament::input.wrapper label="Titre partie 1">
                    <x-filament::input type="text" wire:model="data.about_title1" placeholder="Un héritage français," />
                </x-filament::input.wrapper>
                <x-filament::input.wrapper label="Titre partie 2 (or)">
                    <x-filament::input type="text" wire:model="data.about_title2" placeholder="une exigence absolue" />
                </x-filament::input.wrapper>
                <div class="md:col-span-2">
                    <x-filament::input.wrapper label="Paragraphe 1">
                        <x-filament::input type="textarea" wire:model="data.about_p1" rows="3" />
                    </x-filament::input.wrapper>
                </div>
                <div class="md:col-span-2">
                    <x-filament::input.wrapper label="Paragraphe 2">
                        <x-filament::input type="textarea" wire:model="data.about_p2" rows="3" />
                    </x-filament::input.wrapper>
                </div>
                <x-filament::input.wrapper label="Certification">
                    <x-filament::input type="text" wire:model="data.about_cert" placeholder="Certifié IGI · GIA · LFG" />
                </x-filament::input.wrapper>
                <x-filament::input.wrapper label="Sous-titre certification">
                    <x-filament::input type="text" wire:model="data.about_certSub" placeholder="Authenticité garantie" />
                </x-filament::input.wrapper>
            </div>

            <div class="mt-6">
                <h3 class="text-lg font-medium mb-4 dark:text-gray-200">Statistiques (4 chiffres)</h3>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="p-3 border rounded-lg space-y-2 dark:border-gray-700">
                        <span class="text-xs font-medium text-gray-500">Stat 1 — Pierres</span>
                        <x-filament::input.wrapper label="Valeur">
                            <x-filament::input type="text" wire:model="data.stats_stonesValue" placeholder="2 500+" />
                        </x-filament::input.wrapper>
                        <x-filament::input.wrapper label="Label">
                            <x-filament::input type="text" wire:model="data.stats_stones" placeholder="Pierres disponibles" />
                        </x-filament::input.wrapper>
                    </div>
                    <div class="p-3 border rounded-lg space-y-2 dark:border-gray-700">
                        <span class="text-xs font-medium text-gray-500">Stat 2 — Bijoux</span>
                        <x-filament::input.wrapper label="Valeur">
                            <x-filament::input type="text" wire:model="data.stats_jewelsValue" placeholder="850+" />
                        </x-filament::input.wrapper>
                        <x-filament::input.wrapper label="Label">
                            <x-filament::input type="text" wire:model="data.stats_jewels" placeholder="Bijoux en collection" />
                        </x-filament::input.wrapper>
                    </div>
                    <div class="p-3 border rounded-lg space-y-2 dark:border-gray-700">
                        <span class="text-xs font-medium text-gray-500">Stat 3 — Clients</span>
                        <x-filament::input.wrapper label="Valeur">
                            <x-filament::input type="text" wire:model="data.stats_clientsValue" placeholder="12 000" />
                        </x-filament::input.wrapper>
                        <x-filament::input.wrapper label="Label">
                            <x-filament::input type="text" wire:model="data.stats_clients" placeholder="Clients satisfaits" />
                        </x-filament::input.wrapper>
                    </div>
                    <div class="p-3 border rounded-lg space-y-2 dark:border-gray-700">
                        <span class="text-xs font-medium text-gray-500">Stat 4 — Expertise</span>
                        <x-filament::input.wrapper label="Valeur">
                            <x-filament::input type="text" wire:model="data.stats_yearsValue" placeholder="16 ans" />
                        </x-filament::input.wrapper>
                        <x-filament::input.wrapper label="Label">
                            <x-filament::input type="text" wire:model="data.stats_years" placeholder="D'expertise" />
                        </x-filament::input.wrapper>
                    </div>
                </div>
            </div>
        </x-filament::section>

        {{-- Section Nos Engagements --}}
        <x-filament::section heading="Section Nos Engagements" description="4 valeurs affichées sur le portail" collapsible>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <x-filament::input.wrapper label="Surtitre section">
                    <x-filament::input type="text" wire:model="data.values_kicker" placeholder="Nos Engagements" />
                </x-filament::input.wrapper>
                <x-filament::input.wrapper label="Titre partie 1">
                    <x-filament::input type="text" wire:model="data.values_title1" placeholder="Quatre valeurs," />
                </x-filament::input.wrapper>
                <x-filament::input.wrapper label="Titre partie 2 (or)">
                    <x-filament::input type="text" wire:model="data.values_title2" placeholder="une promesse" />
                </x-filament::input.wrapper>
            </div>

            @php $valKeys = ['v1' => 1, 'v2' => 2, 'v3' => 3, 'v4' => 4]; @endphp
            @foreach($valKeys as $vk => $vi)
                <div class="p-4 border rounded-lg space-y-4 mb-4 border-gray-200 dark:border-gray-700">
                    <h3 class="text-sm font-medium text-gray-600 dark:text-gray-300">Valeur {{ $vi }}</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <x-filament::input.wrapper label="Icône Lucide">
                            <x-filament::input type="text" wire:model="data.values_{{ $vk }}icon" placeholder="ShieldCheck" />
                        </x-filament::input.wrapper>
                        <x-filament::input.wrapper label="Titre">
                            <x-filament::input type="text" wire:model="data.values_{{ $vk }}t" placeholder="Titre" />
                        </x-filament::input.wrapper>
                        <x-filament::input.wrapper label="Description">
                            <x-filament::input type="text" wire:model="data.values_{{ $vk }}d" placeholder="Description" />
                        </x-filament::input.wrapper>
                    </div>
                </div>
            @endforeach
        </x-filament::section>

        {{-- Footer --}}
        <x-filament::section heading="Footer" description="Pied de page" collapsible>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <x-filament::input.wrapper label="Tagline">
                        <x-filament::input type="textarea" wire:model="data.footer_tagline" rows="2" />
                    </x-filament::input.wrapper>
                </div>
                <x-filament::input.wrapper label="Titre colonne Univers">
                    <x-filament::input type="text" wire:model="data.footer_universes" placeholder="Nos Univers" />
                </x-filament::input.wrapper>
                <x-filament::input.wrapper label="Titre colonne Contact">
                    <x-filament::input type="text" wire:model="data.footer_contactTitle" placeholder="Contact" />
                </x-filament::input.wrapper>
                <x-filament::input.wrapper label="Texte droits réservés">
                    <x-filament::input type="text" wire:model="data.footer_rights" placeholder="Tous droits réservés." />
                </x-filament::input.wrapper>
                <x-filament::input.wrapper label="Texte signature">
                    <x-filament::input type="text" wire:model="data.footer_made" placeholder="Made with passion in Paris" />
                </x-filament::input.wrapper>
            </div>
        </x-filament::section>

        <div class="flex justify-end">
            <x-filament::button wire:click="save" color="primary" size="lg">
                Enregistrer le contenu du portail
            </x-filament::button>
        </div>

    </div>
</x-filament-panels::page>
