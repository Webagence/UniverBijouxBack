<?php

namespace App\Filament\Pages;

use App\Models\ContentBlock;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Storage;
use Livewire\WithFileUploads;

class ManageContent extends Page
{
    use WithFileUploads;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Pages & Contenu';

    protected static ?string $navigationGroup = 'Contenu';

    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.manage-content';

    public ?array $heroData = [];

    public ?array $atelierData = [];

    public ?array $promisesData = [];

    public ?array $categoriesSectionData = [];

    public ?array $productGridSectionData = [];

    public ?array $newByUniverseSectionData = [];

    public ?array $testimonialsSectionData = [];

    public ?array $contactPageData = [];

    public ?array $faqPageHeaderData = [];

    public ?array $legalContentData = [];

    public ?string $heroImage = null;

    public ?string $atelierImage = null;

    public $heroImageFile = null;

    public $atelierImageFile = null;

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

        $promisesBlock = ContentBlock::where('key', 'promises')->first();
        $this->promisesData = $promisesBlock?->data ?? [];

        $categoriesBlock = ContentBlock::where('key', 'categories_section')->first();
        $this->categoriesSectionData = $categoriesBlock?->data ?? [];

        $productGridBlock = ContentBlock::where('key', 'product_grid_section')->first();
        $this->productGridSectionData = $productGridBlock?->data ?? [];

        $newByUniverseBlock = ContentBlock::where('key', 'new_by_universe_section')->first();
        $this->newByUniverseSectionData = $newByUniverseBlock?->data ?? [];

        $testimonialsSectionBlock = ContentBlock::where('key', 'testimonials_section')->first();
        $this->testimonialsSectionData = $testimonialsSectionBlock?->data ?? [];

        $contactPageBlock = ContentBlock::where('key', 'contact_page')->first();
        $this->contactPageData = $contactPageBlock?->data ?? [];

        $faqPageHeaderBlock = ContentBlock::where('key', 'faq_page_header')->first();
        $this->faqPageHeaderData = $faqPageHeaderBlock?->data ?? [];

        $this->legalContentData = [
            'legal' => ContentBlock::where('key', 'legal_legal')->first()?->data ?? [
                'title' => 'Mentions légales',
                'sections' => [
                    ['heading' => 'Éditeur', 'body' => 'MAISON LUNE · 12 rue Saint-Honoré, 75001 Paris'],
                    ['heading' => 'Directeur de la publication', 'body' => 'Responsable de la publication.'],
                    ['heading' => 'Hébergeur', 'body' => 'Hébergement et distribution en Europe.'],
                    ['heading' => 'Contact', 'body' => 'pro@maisonlune.fr · +33 1 42 00 00 00'],
                    ['heading' => 'Propriété intellectuelle', 'body' => "L'ensemble du site (textes, images, logos, graphismes) est protégé par le droit d'auteur et est la propriété exclusive de MAISON LUNE."],
                ],
            ],
            'cgv' => ContentBlock::where('key', 'legal_cgv')->first()?->data ?? [
                'eyebrow' => 'Conditions',
                'title' => 'Conditions générales de vente',
                'subtitle' => 'Applicables aux clients professionnels de MAISON LUNE.',
                'sections' => [
                    ['heading' => '1. Objet', 'body' => 'Les présentes CGV régissent les ventes conclues entre MAISON LUNE et ses clients professionnels titulaires d\'un numéro SIRET valide.'],
                    ['heading' => '2. Prix', 'body' => 'Tous les prix sont exprimés en euros hors taxes (HT). La TVA au taux de 20% s\'applique sauf pour les clients intracommunautaires fournissant un numéro de TVA valide.'],
                    ['heading' => '3. Commandes', 'body' => 'La quantité minimale est de 3 pièces par référence. Les commandes sont fermes après validation. Franco de port à partir de 300€ HT en France métropolitaine.'],
                    ['heading' => '4. Paiement', 'body' => 'Paiement à 30 jours date de facture pour les comptes validés. Premier achat : paiement à la commande par virement ou carte.'],
                    ['heading' => '5. Livraison', 'body' => 'Délai standard de 48h après validation pour les pièces en stock. Les risques sont transférés à la remise au transporteur.'],
                    ['heading' => '6. Retours & garantie', 'body' => 'Garantie à vie sur les défauts de fabrication. Retours acceptés sous 14 jours, produits en parfait état et dans leur emballage d\'origine.'],
                    ['heading' => '7. Propriété intellectuelle', 'body' => 'Tous les visuels, fiches produit et marques restent la propriété exclusive de MAISON LUNE.'],
                    ['heading' => '8. Litiges', 'body' => 'Le droit français s\'applique. Tout litige relève de la compétence exclusive du Tribunal de Commerce de Paris.'],
                ],
            ],
            'privacy' => ContentBlock::where('key', 'legal_privacy')->first()?->data ?? [
                'title' => 'Politique de confidentialité',
                'subtitle' => 'Nous traitons vos données personnelles conformément au RGPD. MAISON LUNE.',
                'sections' => [
                    ['heading' => 'Données collectées', 'body' => 'Raison sociale, SIRET, nom du contact, email et mot de passe chiffré, historique des commandes.'],
                    ['heading' => 'Finalités', 'body' => 'Gestion du compte professionnel, traitement des commandes, facturation, service client, relation commerciale.'],
                    ['heading' => 'Durée de conservation', 'body' => 'Données de compte : durée de la relation commerciale + 3 ans. Données de facturation : 10 ans (obligation légale).'],
                    ['heading' => 'Vos droits', 'body' => 'Accès, rectification, effacement, portabilité, opposition : écrivez à pro@maisonlune.fr.'],
                    ['heading' => 'Cookies', 'body' => 'Nous utilisons uniquement des cookies strictement nécessaires au bon fonctionnement du site (panier, session).'],
                ],
            ],
            'shipping' => ContentBlock::where('key', 'legal_shipping')->first()?->data ?? [
                'eyebrow' => 'Infos pro',
                'title' => 'Livraison & retours',
                'subtitle' => 'Nos engagements logistiques pour nos partenaires revendeurs. MAISON LUNE.',
                'sections' => [
                    ['heading' => 'Délais & zones', 'body' => 'France métropolitaine : 48h ouvrées. UE : 3 à 5 jours ouvrés. International : sur devis.'],
                    ['heading' => 'Frais de port', 'body' => 'Offerts dès 300€ HT en France. 12€ HT en dessous. UE : 25€ HT forfaitaire.'],
                    ['heading' => 'Suivi', 'body' => 'Un email de suivi Colissimo ou Chronopost vous est envoyé dès l\'expédition depuis notre atelier.'],
                    ['heading' => 'Retours', 'body' => 'Retours acceptés sous 14 jours, produits non portés et dans leur écrin d\'origine. Demandez un bon de retour via votre espace pro.'],
                    ['heading' => 'Garantie', 'body' => 'Tous nos bijoux sont garantis à vie contre les défauts de fabrication. SAV dédié pour nos revendeurs.'],
                ],
            ],
        ];
    }

    public function saveHero(): void
    {
        if ($this->heroImageFile) {
            $this->validate([
                'heroImageFile' => 'image|max:5120',
            ]);
            $path = $this->heroImageFile->store('content/hero', 'public');
            $this->heroImage = Storage::url($path);
            $this->heroData['image'] = $this->heroImage;
            $this->heroImageFile = null;
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
        if ($this->atelierImageFile) {
            $this->validate([
                'atelierImageFile' => 'image|max:5120',
            ]);
            $path = $this->atelierImageFile->store('content/atelier', 'public');
            $this->atelierImage = Storage::url($path);
            $this->atelierData['image'] = $this->atelierImage;
            $this->atelierImageFile = null;
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

    public function savePromises(): void
    {
        ContentBlock::updateOrCreate(
            ['key' => 'promises'],
            ['data' => $this->promisesData]
        );

        Notification::make()
            ->title('Section Promesses enregistrée')
            ->success()
            ->send();
    }

    public function addPromise(): void
    {
        $this->promisesData[] = [
            'icon' => 'Truck',
            'title' => '',
            'text' => '',
        ];
    }

    public function removePromise(int $index): void
    {
        array_splice($this->promisesData, $index, 1);
    }

    public function saveCategoriesSection(): void
    {
        ContentBlock::updateOrCreate(
            ['key' => 'categories_section'],
            ['data' => $this->categoriesSectionData]
        );

        Notification::make()
            ->title('Section Catégories enregistrée')
            ->success()
            ->send();
    }

    public function saveProductGridSection(): void
    {
        ContentBlock::updateOrCreate(
            ['key' => 'product_grid_section'],
            ['data' => $this->productGridSectionData]
        );

        Notification::make()
            ->title('Section Best-sellers enregistrée')
            ->success()
            ->send();
    }

    public function saveNewByUniverseSection(): void
    {
        ContentBlock::updateOrCreate(
            ['key' => 'new_by_universe_section'],
            ['data' => $this->newByUniverseSectionData]
        );

        Notification::make()
            ->title('Section Nouveautés enregistrée')
            ->success()
            ->send();
    }

    public function saveTestimonialsSection(): void
    {
        ContentBlock::updateOrCreate(
            ['key' => 'testimonials_section'],
            ['data' => $this->testimonialsSectionData]
        );

        Notification::make()
            ->title('Section Témoignages enregistrée')
            ->success()
            ->send();
    }

    public function saveContactPage(): void
    {
        ContentBlock::updateOrCreate(
            ['key' => 'contact_page'],
            ['data' => $this->contactPageData]
        );

        Notification::make()
            ->title('Page Contact enregistrée')
            ->success()
            ->send();
    }

    public function saveFAQPageHeader(): void
    {
        ContentBlock::updateOrCreate(
            ['key' => 'faq_page_header'],
            ['data' => $this->faqPageHeaderData]
        );

        Notification::make()
            ->title('En-tête FAQ enregistré')
            ->success()
            ->send();
    }

    public function saveLegalContent(): void
    {
        foreach (['legal', 'cgv', 'privacy', 'shipping'] as $page) {
            if (isset($this->legalContentData[$page])) {
                ContentBlock::updateOrCreate(
                    ['key' => "legal_{$page}"],
                    ['data' => $this->legalContentData[$page]]
                );
            }
        }

        Notification::make()
            ->title('Pages légales enregistrées')
            ->success()
            ->send();
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
