<?php

namespace Database\Seeders;

use App\Models\ContentBlock;
use App\Models\FaqItem;
use App\Models\SiteSetting;
use App\Models\Testimonial;
use Illuminate\Database\Seeder;

class ContentSeeder extends Seeder
{
    public function run(): void
    {
        ContentBlock::create([
            'key' => 'hero',
            'data' => [
                'eyebrow' => 'Grossiste bijoux · Collection Printemps 2026',
                'titleLine1' => 'Le bijou',
                'titleEm' => 'français',
                'titleLine2' => 'pour les pros',
                'paragraph' => 'Bijoux délicats fabriqués en France, distribués en exclusivité aux revendeurs, concept-stores et instituts. Tarifs HT, quantités minimales réduites, réassort 48h.',
                'ctaPrimary' => 'Voir le catalogue',
                'ctaSecondary' => 'Ouvrir un compte pro',
                'stat1Value' => '850+',
                'stat1Label' => 'revendeurs partenaires',
                'stat2Value' => '48h',
                'stat2Label' => 'délai de livraison',
                'quote' => 'Un bijou est un sourire qui ne s\'efface jamais.',
            ],
        ]);

        ContentBlock::create([
            'key' => 'atelier',
            'data' => [
                'eyebrow' => 'Notre atelier',
                'title' => 'Le savoir-faire,',
                'titleEm' => 'geste après geste',
                'paragraph1' => 'Depuis 2016, Maison Lune façonne des bijoux pensés pour durer. Chaque pièce naît d\'un croquis, prend vie sous les mains de nos artisans, et se révèle dans un éclat précieux.',
                'paragraph2' => 'Or recyclé, pierres éthiques, finitions à la main : nous avons choisi la lenteur comme luxe ultime.',
                'badgeNumber' => '12',
                'badgeLabel' => 'Artisans dans notre atelier parisien',
            ],
        ]);

        Testimonial::create([
            'author' => 'Camille L.',
            'shop' => 'Concept-store Ondine · Lyon',
            'quote' => 'Un catalogue qui se vend tout seul. Mes clientes reviennent pour les nouveautés Maison Lune chaque saison.',
            'rating' => 5,
            'display_order' => 1,
            'active' => true,
        ]);

        Testimonial::create([
            'author' => 'Marie D.',
            'shop' => 'Boutique Écrin · Bordeaux',
            'quote' => 'Qualité irréprochable, SAV réactif et les réassorts arrivent toujours en 48h. Un vrai partenaire.',
            'rating' => 5,
            'display_order' => 2,
            'active' => true,
        ]);

        Testimonial::create([
            'author' => 'Léa M.',
            'shop' => 'Institut Belle Étoile · Paris',
            'quote' => 'Les marges sont confortables et les pièces sont vraiment différenciantes. Indispensable en boutique.',
            'rating' => 5,
            'display_order' => 3,
            'active' => true,
        ]);

        $faqs = [
            ['question' => 'Qui peut commander sur Maison Lune ?', 'answer' => 'Le site est strictement réservé aux professionnels titulaires d\'un numéro SIRET valide : revendeurs, concept-stores, instituts, hôtels.', 'category' => 'general', 'display_order' => 1],
            ['question' => 'Quelle est la quantité minimale de commande ?', 'answer' => '3 pièces par référence. Pas de minimum global pour une première commande.', 'category' => 'commande', 'display_order' => 2],
            ['question' => 'Quels sont vos délais de livraison ?', 'answer' => '48h ouvrées après validation pour les pièces en stock. Envoi depuis notre atelier parisien.', 'category' => 'livraison', 'display_order' => 3],
            ['question' => 'Proposez-vous des tarifs dégressifs ?', 'answer' => 'Oui : -10% dès 500€ HT, -15% dès 1 500€ HT, -20% dès 3 000€ HT. Remises appliquées automatiquement au panier.', 'category' => 'tarifs', 'display_order' => 4],
            ['question' => 'Comment sont fabriqués vos bijoux ?', 'answer' => 'Tous nos bijoux sont dessinés et fabriqués à la main dans notre atelier parisien, en or recyclé et avec des pierres éthiques.', 'category' => 'produits', 'display_order' => 5],
            ['question' => 'Avez-vous un showroom ?', 'answer' => 'Oui, à Paris 8e, sur rendez-vous. Contactez notre équipe commerciale pour réserver un créneau.', 'category' => 'general', 'display_order' => 6],
            ['question' => 'Quelles sont les conditions de paiement ?', 'answer' => 'Première commande : paiement à la commande. Ensuite, paiement à 30 jours date de facture pour les comptes validés.', 'category' => 'paiement', 'display_order' => 7],
            ['question' => 'Proposez-vous un support marketing ?', 'answer' => 'Oui : photos HD, fiches produit, PLV, formations vendeurs. Disponibles sur demande auprès de votre commercial dédié.', 'category' => 'general', 'display_order' => 8],
        ];

        foreach ($faqs as $faq) {
            FaqItem::create([
                'question' => $faq['question'],
                'answer' => $faq['answer'],
                'category' => $faq['category'],
                'display_order' => $faq['display_order'],
                'active' => true,
            ]);
        }

        SiteSetting::create([
            'key' => 'general',
            'value' => [
                'siteName' => 'MAISON LUNE',
                'tagline' => 'Grossiste bijoux français',
                'email' => 'pro@maisonlune.fr',
                'phone' => '+33 1 42 00 00 00',
                'address' => '12 rue Saint-Honoré, 75001 Paris',
                'freeShippingFrom' => '300',
            ],
        ]);
    }
}
