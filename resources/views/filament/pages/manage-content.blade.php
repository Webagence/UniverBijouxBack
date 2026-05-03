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
            <h3 class="text-lg font-medium mb-4 dark:text-gray-200">Statistiques</h3>
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

        <div class="mt-6">
            <h3 class="text-lg font-medium mb-4 dark:text-gray-200">Statistiques (3 badges)</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @for($i = 0; $i < 3; $i++)
                    <div class="p-3 border rounded-lg space-y-2 dark:border-gray-700">
                        <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Badge {{ $i + 1 }}</span>
                        <x-filament::input.wrapper label="Label">
                            <x-filament::input
                                type="text"
                                wire:model="atelierData.stats.{{ $i }}.0"
                                placeholder="Or recyclé"
                            />
                        </x-filament::input.wrapper>
                        <x-filament::input.wrapper label="Valeur">
                            <x-filament::input
                                type="text"
                                wire:model="atelierData.stats.{{ $i }}.1"
                                placeholder="100%"
                            />
                        </x-filament::input.wrapper>
                    </div>
                @endfor
            </div>
        </div>

        <div class="mt-6 flex justify-end">
            <x-filament::button wire:click="saveAtelier" color="primary">
                Enregistrer la section Atelier
            </x-filament::button>
        </div>
    </x-filament::section>

    {{-- Promises Section --}}
    <x-filament::section heading="Section Promesses (Accueil)" description="Les 4 engagements affichés sur la page d'accueil" class="mt-8">
        <div class="space-y-4">
            @foreach($promisesData as $index => $promise)
                <div class="p-4 border rounded-lg space-y-3 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Promesse {{ $index + 1 }}</span>
                        <x-filament::button wire:click="removePromise({{ $index }})" color="danger" size="sm" outlined>
                            <x-heroicon-m-trash class="w-4 h-4" />
                        </x-filament::button>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <x-filament::input.wrapper label="Icône (Lucide)">
                            <x-filament::input
                                type="text"
                                wire:model="promisesData.{{ $index }}.icon"
                                placeholder="Truck"
                            />
                        </x-filament::input.wrapper>
                        <x-filament::input.wrapper label="Titre">
                            <x-filament::input
                                type="text"
                                wire:model="promisesData.{{ $index }}.title"
                                placeholder="Livraison 48h"
                            />
                        </x-filament::input.wrapper>
                        <x-filament::input.wrapper label="Description">
                            <x-filament::input
                                type="text"
                                wire:model="promisesData.{{ $index }}.text"
                                placeholder="Franco dès 300€ HT"
                            />
                        </x-filament::input.wrapper>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="mt-4 flex justify-between">
            <x-filament::button wire:click="addPromise" color="gray" size="sm" outlined>
                <x-heroicon-m-plus class="w-4 h-4 mr-1" />
                Ajouter une promesse
            </x-filament::button>
            <x-filament::button wire:click="savePromises" color="primary">
                Enregistrer les promesses
            </x-filament::button>
        </div>
    </x-filament::section>

    {{-- Categories Section Header --}}
    <x-filament::section heading="Section Catégories (Accueil)" description="Titre et description de la section univers" class="mt-8">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <x-filament::input.wrapper label="Surtitre (Eyebrow)">
                <x-filament::input
                    type="text"
                    wire:model="categoriesSectionData.eyebrow"
                    placeholder="Univers"
                />
            </x-filament::input.wrapper>
            <x-filament::input.wrapper label="Titre - Partie 1">
                <x-filament::input
                    type="text"
                    wire:model="categoriesSectionData.heading"
                    placeholder="Explorez nos"
                />
            </x-filament::input.wrapper>
            <x-filament::input.wrapper label="Titre - Mise en avant (or)">
                <x-filament::input
                    type="text"
                    wire:model="categoriesSectionData.headingEm"
                    placeholder="collections"
                />
            </x-filament::input.wrapper>
            <x-filament::input.wrapper label="Description">
                <x-filament::input
                    type="text"
                    wire:model="categoriesSectionData.description"
                    placeholder="Chaque pièce est dessinée et fabriquée dans nos ateliers français."
                />
            </x-filament::input.wrapper>
        </div>
        <div class="mt-6 flex justify-end">
            <x-filament::button wire:click="saveCategoriesSection" color="primary">
                Enregistrer la section Catégories
            </x-filament::button>
        </div>
    </x-filament::section>

    {{-- Product Grid Section Header --}}
    <x-filament::section heading="Section Best-sellers (Accueil)" description="Titre et description de la section best-sellers" class="mt-8">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <x-filament::input.wrapper label="Surtitre (Eyebrow)">
                <x-filament::input
                    type="text"
                    wire:model="productGridSectionData.eyebrow"
                    placeholder="Best-sellers"
                />
            </x-filament::input.wrapper>
            <x-filament::input.wrapper label="Titre - Partie 1">
                <x-filament::input
                    type="text"
                    wire:model="productGridSectionData.heading"
                    placeholder="Les pièces que vos clients"
                />
            </x-filament::input.wrapper>
            <x-filament::input.wrapper label="Titre - Mise en avant (or)">
                <x-filament::input
                    type="text"
                    wire:model="productGridSectionData.headingEm"
                    placeholder="adorent"
                />
            </x-filament::input.wrapper>
            <x-filament::input.wrapper label="Description">
                <x-filament::input
                    type="text"
                    wire:model="productGridSectionData.description"
                    placeholder="Les références les plus commandées par notre réseau de revendeurs."
                />
            </x-filament::input.wrapper>
            <x-filament::input.wrapper label="Texte du lien CTA">
                <x-filament::input
                    type="text"
                    wire:model="productGridSectionData.ctaText"
                    placeholder="Voir tout le catalogue →"
                />
            </x-filament::input.wrapper>
        </div>
        <div class="mt-6 flex justify-end">
            <x-filament::button wire:click="saveProductGridSection" color="primary">
                Enregistrer la section Best-sellers
            </x-filament::button>
        </div>
    </x-filament::section>

    {{-- New By Universe Section Header --}}
    <x-filament::section heading="Section Nouveautés par univers" description="Titre et description de la section nouveautés" class="mt-8">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <x-filament::input.wrapper label="Surtitre (Eyebrow)">
                <x-filament::input
                    type="text"
                    wire:model="newByUniverseSectionData.eyebrow"
                    placeholder="Nouveautés par univers"
                />
            </x-filament::input.wrapper>
            <x-filament::input.wrapper label="Titre - Partie 1">
                <x-filament::input
                    type="text"
                    wire:model="newByUniverseSectionData.heading"
                    placeholder="Les"
                />
            </x-filament::input.wrapper>
            <x-filament::input.wrapper label="Titre - Mise en avant (or)">
                <x-filament::input
                    type="text"
                    wire:model="newByUniverseSectionData.headingEm"
                    placeholder="nouvelles pièces"
                />
            </x-filament::input.wrapper>
            <x-filament::input.wrapper label="Description">
                <x-filament::input
                    type="text"
                    wire:model="newByUniverseSectionData.description"
                    placeholder="Les 4 dernières références dans chacun de nos univers."
                />
            </x-filament::input.wrapper>
            <x-filament::input.wrapper label="Texte du lien CTA">
                <x-filament::input
                    type="text"
                    wire:model="newByUniverseSectionData.ctaText"
                    placeholder="Voir tout →"
                />
            </x-filament::input.wrapper>
        </div>
        <div class="mt-6 flex justify-end">
            <x-filament::button wire:click="saveNewByUniverseSection" color="primary">
                Enregistrer la section Nouveautés
            </x-filament::button>
        </div>
    </x-filament::section>

    {{-- Testimonials Section Header --}}
    <x-filament::section heading="Section Témoignages (Accueil)" description="Titre et compteur de la section témoignages" class="mt-8">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <x-filament::input.wrapper label="Surtitre (Eyebrow)">
                <x-filament::input
                    type="text"
                    wire:model="testimonialsSectionData.eyebrow"
                    placeholder="Nos revendeurs en parlent"
                />
            </x-filament::input.wrapper>
            <x-filament::input.wrapper label="Titre - Partie 1">
                <x-filament::input
                    type="text"
                    wire:model="testimonialsSectionData.heading"
                    placeholder="850 partenaires nous"
                />
            </x-filament::input.wrapper>
            <x-filament::input.wrapper label="Titre - Mise en avant (or)">
                <x-filament::input
                    type="text"
                    wire:model="testimonialsSectionData.headingEm"
                    placeholder="font confiance"
                />
            </x-filament::input.wrapper>
        </div>
        <div class="mt-6 flex justify-end">
            <x-filament::button wire:click="saveTestimonialsSection" color="primary">
                Enregistrer la section Témoignages
            </x-filament::button>
        </div>
    </x-filament::section>

    {{-- Contact Page --}}
    <x-filament::section heading="Page Contact" description="Contenu de la page de contact" class="mt-8">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <x-filament::input.wrapper label="Surtitre (Eyebrow)">
                <x-filament::input
                    type="text"
                    wire:model="contactPageData.eyebrow"
                    placeholder="Nous écrire"
                />
            </x-filament::input.wrapper>
            <x-filament::input.wrapper label="Titre">
                <x-filament::input
                    type="text"
                    wire:model="contactPageData.title"
                    placeholder="Contact commercial"
                />
            </x-filament::input.wrapper>
            <x-filament::input.wrapper label="Sous-titre" class="md:col-span-2">
                <x-filament::input
                    type="text"
                    wire:model="contactPageData.subtitle"
                    placeholder="Une question sur un produit, un devis, ou une demande de partenariat ?"
                />
            </x-filament::input.wrapper>
            <x-filament::input.wrapper label="Titre Showroom">
                <x-filament::input
                    type="text"
                    wire:model="contactPageData.showroomTitle"
                    placeholder="Showroom sur rendez-vous"
                />
            </x-filament::input.wrapper>
            <x-filament::input.wrapper label="Texte Showroom">
                <x-filament::input
                    type="text"
                    wire:model="contactPageData.showroomText"
                    placeholder="Du lundi au vendredi, 10h–18h. Prenez contact pour venir découvrir les collections en avant-première."
                />
            </x-filament::input.wrapper>
        </div>
        <div class="mt-6 flex justify-end">
            <x-filament::button wire:click="saveContactPage" color="primary">
                Enregistrer la page Contact
            </x-filament::button>
        </div>
    </x-filament::section>

    {{-- FAQ Page Header --}}
    <x-filament::section heading="Page FAQ - En-tête" description="Titre et description de la page FAQ" class="mt-8">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <x-filament::input.wrapper label="Surtitre (Eyebrow)">
                <x-filament::input
                    type="text"
                    wire:model="faqPageHeaderData.eyebrow"
                    placeholder="Aide"
                />
            </x-filament::input.wrapper>
            <x-filament::input.wrapper label="Titre">
                <x-filament::input
                    type="text"
                    wire:model="faqPageHeaderData.title"
                    placeholder="Questions fréquentes"
                />
            </x-filament::input.wrapper>
            <x-filament::input.wrapper label="Sous-titre" class="md:col-span-2">
                <x-filament::input
                    type="text"
                    wire:model="faqPageHeaderData.subtitle"
                    placeholder="Tout ce que les revendeurs nous demandent le plus souvent."
                />
            </x-filament::input.wrapper>
        </div>
        <div class="mt-6 flex justify-end">
            <x-filament::button wire:click="saveFAQPageHeader" color="primary">
                Enregistrer l'en-tête FAQ
            </x-filament::button>
        </div>
    </x-filament::section>

    {{-- Legal Pages --}}
    <x-filament::section heading="Pages légales" description="Contenu des pages Mentions légales, CGV, Confidentialité et Livraison" class="mt-8">
        <div class="space-y-8">
            {{-- Mentions légales --}}
            <div class="p-4 border rounded-lg space-y-4 dark:border-gray-700">
                <h3 class="text-lg font-medium text-gray-700 dark:text-gray-200">Mentions légales</h3>
                <x-filament::input.wrapper label="Titre">
                    <x-filament::input
                        type="text"
                        wire:model="legalContentData.legal.title"
                        placeholder="Mentions légales"
                    />
                </x-filament::input.wrapper>
                <div class="space-y-3">
                    <h4 class="text-sm font-medium text-gray-600 dark:text-gray-300">Sections</h4>
                    @foreach($legalContentData['legal']['sections'] ?? [] as $index => $section)
                        <div class="p-3 bg-gray-50 dark:bg-gray-800/50 rounded space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Section {{ $index + 1 }}</span>
                            </div>
                            <x-filament::input.wrapper label="Titre de section">
                                <x-filament::input
                                    type="text"
                                    wire:model="legalContentData.legal.sections.{{ $index }}.heading"
                                    placeholder="Éditeur"
                                />
                            </x-filament::input.wrapper>
                            <x-filament::input.wrapper label="Contenu">
                                <x-filament::input
                                    type="textarea"
                                    wire:model="legalContentData.legal.sections.{{ $index }}.body"
                                    rows="2"
                                    placeholder="Contenu de la section"
                                />
                            </x-filament::input.wrapper>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- CGV --}}
            <div class="p-4 border rounded-lg space-y-4 dark:border-gray-700">
                <h3 class="text-lg font-medium text-gray-700 dark:text-gray-200">Conditions générales de vente (CGV)</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <x-filament::input.wrapper label="Surtitre (Eyebrow)">
                        <x-filament::input
                            type="text"
                            wire:model="legalContentData.cgv.eyebrow"
                            placeholder="Conditions"
                        />
                    </x-filament::input.wrapper>
                    <x-filament::input.wrapper label="Titre">
                        <x-filament::input
                            type="text"
                            wire:model="legalContentData.cgv.title"
                            placeholder="Conditions générales de vente"
                        />
                    </x-filament::input.wrapper>
                    <x-filament::input.wrapper label="Sous-titre" class="md:col-span-2">
                        <x-filament::input
                            type="text"
                            wire:model="legalContentData.cgv.subtitle"
                            placeholder="Applicables aux clients professionnels de MAISON LUNE."
                        />
                    </x-filament::input.wrapper>
                </div>
                <div class="space-y-3">
                    <h4 class="text-sm font-medium text-gray-600 dark:text-gray-300">Sections</h4>
                    @foreach($legalContentData['cgv']['sections'] ?? [] as $index => $section)
                        <div class="p-3 bg-gray-50 dark:bg-gray-800/50 rounded space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Section {{ $index + 1 }}</span>
                            </div>
                            <x-filament::input.wrapper label="Titre de section">
                                <x-filament::input
                                    type="text"
                                    wire:model="legalContentData.cgv.sections.{{ $index }}.heading"
                                    placeholder="1. Objet"
                                />
                            </x-filament::input.wrapper>
                            <x-filament::input.wrapper label="Contenu">
                                <x-filament::input
                                    type="textarea"
                                    wire:model="legalContentData.cgv.sections.{{ $index }}.body"
                                    rows="2"
                                    placeholder="Contenu de la section"
                                />
                            </x-filament::input.wrapper>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Confidentialité --}}
            <div class="p-4 border rounded-lg space-y-4 dark:border-gray-700">
                <h3 class="text-lg font-medium text-gray-700 dark:text-gray-200">Politique de confidentialité</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <x-filament::input.wrapper label="Titre">
                        <x-filament::input
                            type="text"
                            wire:model="legalContentData.privacy.title"
                            placeholder="Politique de confidentialité"
                        />
                    </x-filament::input.wrapper>
                    <x-filament::input.wrapper label="Sous-titre">
                        <x-filament::input
                            type="text"
                            wire:model="legalContentData.privacy.subtitle"
                            placeholder="Nous traitons vos données personnelles conformément au RGPD."
                        />
                    </x-filament::input.wrapper>
                </div>
                <div class="space-y-3">
                    <h4 class="text-sm font-medium text-gray-600 dark:text-gray-300">Sections</h4>
                    @foreach($legalContentData['privacy']['sections'] ?? [] as $index => $section)
                        <div class="p-3 bg-gray-50 dark:bg-gray-800/50 rounded space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Section {{ $index + 1 }}</span>
                            </div>
                            <x-filament::input.wrapper label="Titre de section">
                                <x-filament::input
                                    type="text"
                                    wire:model="legalContentData.privacy.sections.{{ $index }}.heading"
                                    placeholder="Données collectées"
                                />
                            </x-filament::input.wrapper>
                            <x-filament::input.wrapper label="Contenu">
                                <x-filament::input
                                    type="textarea"
                                    wire:model="legalContentData.privacy.sections.{{ $index }}.body"
                                    rows="2"
                                    placeholder="Contenu de la section"
                                />
                            </x-filament::input.wrapper>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Livraison --}}
            <div class="p-4 border rounded-lg space-y-4 dark:border-gray-700">
                <h3 class="text-lg font-medium text-gray-700 dark:text-gray-200">Livraison & retours</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <x-filament::input.wrapper label="Surtitre (Eyebrow)">
                        <x-filament::input
                            type="text"
                            wire:model="legalContentData.shipping.eyebrow"
                            placeholder="Infos pro"
                        />
                    </x-filament::input.wrapper>
                    <x-filament::input.wrapper label="Titre">
                        <x-filament::input
                            type="text"
                            wire:model="legalContentData.shipping.title"
                            placeholder="Livraison & retours"
                        />
                    </x-filament::input.wrapper>
                    <x-filament::input.wrapper label="Sous-titre" class="md:col-span-2">
                        <x-filament::input
                            type="text"
                            wire:model="legalContentData.shipping.subtitle"
                            placeholder="Nos engagements logistiques pour nos partenaires revendeurs."
                        />
                    </x-filament::input.wrapper>
                </div>
                <div class="space-y-3">
                    <h4 class="text-sm font-medium text-gray-600 dark:text-gray-300">Sections</h4>
                    @foreach($legalContentData['shipping']['sections'] ?? [] as $index => $section)
                        <div class="p-3 bg-gray-50 dark:bg-gray-800/50 rounded space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Section {{ $index + 1 }}</span>
                            </div>
                            <x-filament::input.wrapper label="Titre de section">
                                <x-filament::input
                                    type="text"
                                    wire:model="legalContentData.shipping.sections.{{ $index }}.heading"
                                    placeholder="Délais & zones"
                                />
                            </x-filament::input.wrapper>
                            <x-filament::input.wrapper label="Contenu">
                                <x-filament::input
                                    type="textarea"
                                    wire:model="legalContentData.shipping.sections.{{ $index }}.body"
                                    rows="2"
                                    placeholder="Contenu de la section"
                                />
                            </x-filament::input.wrapper>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="mt-6 flex justify-end">
            <x-filament::button wire:click="saveLegalContent" color="primary">
                Enregistrer les pages légales
            </x-filament::button>
        </div>
    </x-filament::section>
</x-filament-panels::page>
