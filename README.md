# UniverBijoux - Backend Laravel & Admin Filament

Backend API REST et panel d'administration pour la plateforme de vente B2B de bijoux UniverBijoux.

## Architecture

- **Backend**: Laravel 12
- **Admin Panel**: FilamentPHP 3.3
- **Auth API**: Laravel Sanctum
- **Base de données**: SQLite (dev) / MySQL (prod)

## Prérequis

- PHP 8.3+
- Composer
- Node.js & npm (pour le frontend React séparé)

## Installation

```bash
# Installer les dépendances
composer install

# Copier le fichier d'environnement
cp .env.example .env

# Générer la clé d'application
php artisan key:generate

# Créer la base de données et migrer
php artisan migrate:fresh --seed

# Créer le lien de stockage
php artisan storage:link

# Lancer le serveur
php artisan serve
```

## Accès Admin

- **URL**: http://localhost:8000/admin
- **Email**: admin@univerbijoux.com
- **Password**: password123

## API Endpoints

### Public
| Méthode | Endpoint | Description |
|---------|----------|-------------|
| POST | `/api/auth/register` | Inscription client pro |
| POST | `/api/auth/login` | Connexion |
| GET | `/api/products` | Liste produits (paginée) |
| GET | `/api/products/{slug}` | Détail produit |
| GET | `/api/products/universes` | Liste univers |
| GET | `/api/products/new-arrivals` | Nouveautés |
| GET | `/api/products/bestsellers` | Best-sellers |
| GET | `/api/content/hero` | Contenu hero |
| GET | `/api/content/atelier` | Contenu atelier |
| GET | `/api/content/testimonials` | Témoignages |
| GET | `/api/content/faq` | FAQ |
| GET | `/api/content/settings` | Paramètres site |

### Authentifié (Sanctum)
| Méthode | Endpoint | Description |
|---------|----------|-------------|
| POST | `/api/auth/logout` | Déconnexion |
| GET | `/api/auth/me` | Profil utilisateur |
| PUT | `/api/auth/profile` | Modifier profil |
| GET | `/api/orders` | Mes commandes |
| POST | `/api/orders` | Créer commande |
| GET | `/api/orders/{id}` | Détail commande |
| POST | `/api/orders/{id}/cancel` | Annuler commande |
| GET | `/api/tickets` | Mes tickets |
| POST | `/api/tickets` | Créer ticket |
| GET | `/api/tickets/{id}` | Détail ticket |
| POST | `/api/tickets/{id}/reply` | Répondre ticket |

### Admin (role:admin)
| Méthode | Endpoint | Description |
|---------|----------|-------------|
| POST | `/api/uploads/image` | Upload image |
| POST | `/api/uploads/multiple` | Upload multiple |

## Structure de la base de données

### Tables principales
- `users` - Utilisateurs (clients + admin)
- `roles` / `user_roles` - Gestion des rôles
- `universes` - Catégories de produits
- `products` - Catalogue produits
- `orders` / `order_items` - Commandes
- `invoices` - Factures
- `tickets` / `ticket_messages` - Support
- `testimonials` - Témoignages
- `faq_items` - FAQ
- `content_blocks` - Contenu éditorial
- `site_settings` - Paramètres

## Comptes de test

| Email | Password | Rôle | Statut |
|-------|----------|------|--------|
| admin@univerbijoux.com | password123 | Admin | Approuvé |
| contact@boutique-ecrin.fr | password123 | Pro | Approuvé |
| contact@ondine-lyon.fr | password123 | Pro | En attente |

## Fonctionnalités Filament Admin

- **Dashboard**: Stats overview + commandes récentes
- **Catalogue**: Univers, Produits (avec upload d'images)
- **Ventes**: Commandes (avec gestion des statuts)
- **Utilisateurs**: Clients pros (approbation, rôles)
- **Contenu**: Témoignages, FAQ, Contenu éditorial
- **Support**: Tickets de support

## Migration depuis Supabase

Ce projet remplace l'architecture Supabase + React admin par:
- Laravel API pour le frontend React
- FilamentPHP pour l'administration
- Suppression progressive de Supabase
