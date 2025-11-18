# Architecture ComptaPro SaaS

## Vue d'ensemble

ComptaPro est une application SaaS de comptabilité et facturation multi-pays développée avec Laravel 11+, Bootstrap 5 et Vue.js.

## Stack Technique

### Backend
- **Framework**: Laravel 11+
- **PHP**: 8.2+
- **Base de données**: PostgreSQL / MySQL
- **Cache**: Redis
- **Queue**: Redis / Database

### Frontend
- **Framework CSS**: Bootstrap 5
- **Framework JS**: Vue 3 + Inertia.js
- **Routing**: Ziggy (Laravel routes dans Vue)
- **Build**: Vite

### Packages principaux
- **Authentification**: Laravel Sanctum
- **Permissions**: Spatie Laravel Permission
- **PDF**: DomPDF / Laravel Snappy
- **Excel**: Laravel Excel

## Architecture Modulaire

L'application est organisée en modules indépendants :

### Module Core
Gestion des éléments centraux de l'application
- Countries (Pays)
- Companies (Sociétés)
- Users (Utilisateurs)
- Settings (Paramètres)
- Subscriptions (Abonnements)

### Module Accounting
Comptabilité générale
- Accounts (Plan comptable)
- Journals (Journaux)
- Journal Entries (Écritures comptables)
- Fiscal Years (Exercices comptables)
- Closures (Clôtures)

### Module Invoicing
Facturation clients
- Customers (Clients)
- Invoices (Factures)
- Quotes (Devis)
- Credit Notes (Avoirs)
- Payments (Paiements)
- Reminders (Relances)

### Module Tax
Gestion de la TVA
- Tax Rates (Taux de TVA)
- Tax Returns (Déclarations TVA)
- Tax Reports (Rapports fiscaux)
- E-invoicing (Facturation électronique)

### Module Banking
Gestion bancaire
- Bank Accounts (Comptes bancaires)
- Bank Statements (Relevés bancaires)
- Reconciliation (Rapprochement bancaire)

### Module Purchases
Achats et fournisseurs
- Suppliers (Fournisseurs)
- Purchase Orders (Commandes)
- Purchase Invoices (Factures fournisseurs)

### Module Products
Produits et services
- Products (Produits)
- Categories (Catégories)
- Stock (Gestion de stock)

### Module Reporting
Rapports et analytics
- Dashboards
- Financial Reports (Grand livre, Balance, P&L, Bilan)
- Analytics
- Exports (FEC, CSV, Excel, PDF)

## Pays supportés

| Pays | Devise | TVA Standard | Plan Comptable | E-invoicing |
|------|--------|--------------|----------------|-------------|
| 🇧🇪 Belgique | EUR | 21% | PCN | Peppol B2B 2026 |
| 🇫🇷 France | EUR | 20% | PCG 2025 | Chorus Pro |
| 🇨🇭 Suisse | CHF | 8.1% | Plan KMU | Non obligatoire |
| 🇹🇳 Tunisie | TND | 19% | Plan tunisien | TTN / El Fatoora |

## Profils utilisateurs

1. **Super Admin SaaS** - Gestion complète de la plateforme
2. **Admin Société** - Gestion complète d'une société
3. **Responsable Commercial** - Gestion clients et facturation
4. **Gestionnaire Achats** - Gestion fournisseurs et achats
5. **Comptable** - Écritures, rapprochement, TVA
6. **Expert-Comptable Externe** - Lecture seule + ajustements

## Base de données

### Tables principales

#### Core
- `countries` - Pays supportés avec configurations fiscales
- `companies` - Sociétés multi-tenants
- `users` - Utilisateurs
- `company_user` - Relation many-to-many avec rôles

#### Accounting
- `accounts` - Plan comptable par société
- `journals` - Journaux comptables
- `journal_entries` - En-têtes d'écritures
- `journal_entry_lines` - Lignes d'écritures (débit/crédit)

#### Invoicing
- `customers` - Clients
- `invoices` - Factures (devis, factures, avoirs)
- `invoice_lines` - Lignes de factures
- `payments` - Paiements clients

#### Tax
- `tax_rates` - Taux de TVA par pays
- `tax_returns` - Déclarations TVA
- `tax_return_lines` - Lignes de déclaration

#### Banking
- `bank_accounts` - Comptes bancaires
- `bank_statements` - Relevés importés
- `bank_statement_lines` - Lignes de relevés

#### Purchases
- `suppliers` - Fournisseurs
- `purchase_invoices` - Factures fournisseurs
- `purchase_orders` - Commandes achats

#### Products
- `products` - Produits et services
- `product_categories` - Catégories
- `stock_movements` - Mouvements de stock

## Sécurité

- **HTTPS**: Obligatoire en production
- **CSRF**: Protection native Laravel
- **XSS**: Échappement automatique Blade/Vue
- **Authentification**: Sanctum avec 2FA optionnel
- **Permissions**: Système de rôles granulaire Spatie
- **Audit Trail**: Logs complets sur toutes les opérations sensibles
- **RGPD**: Conformité totale

## Performance

- **Caching**: Redis pour sessions et cache applicatif
- **Queue**: Jobs asynchrones pour emails, exports, imports
- **Indexation**: Index optimisés sur company_id, date, status
- **Pagination**: Systématique sur toutes les listes
- **Eager Loading**: Prévention du N+1 problem

## Déploiement

### Prérequis
- PHP 8.2+
- PostgreSQL 14+ ou MySQL 8.0+
- Redis 6+
- Node.js 18+
- Composer 2+

### Installation

```bash
# Cloner le projet
git clone https://github.com/haythemsaa/comptapro-.git
cd comptapro-

# Installer les dépendances PHP
composer install

# Installer les dépendances JS
npm install

# Copier et configurer .env
cp .env.example .env
php artisan key:generate

# Créer la base de données et migrer
php artisan migrate --seed

# Compiler les assets
npm run build

# Démarrer le serveur
php artisan serve
```

## Tests

```bash
# Tests unitaires
php artisan test --testsuite=Unit

# Tests fonctionnels
php artisan test --testsuite=Feature

# Coverage
php artisan test --coverage
```

## Roadmap

### Phase 1 : MVP (4-6 mois) ✅ En cours
- Authentification multi-utilisateurs
- Paramétrage multi-pays (BE, FR)
- Plan comptable pré-configuré
- Clients, fournisseurs, produits
- Facturation complète
- Écritures comptables
- Synthèse TVA simple
- Dashboard basique
- Rapports : Grand livre, Balance

### Phase 2 : Consolidation (3-4 mois)
- Extension Suisse & Tunisie
- Import relevés bancaires
- Rapprochement bancaire
- Factures fournisseurs
- Déclarations TVA avec export XML
- Clôtures périodiques
- Gestion stock simple
- Rapports avancés : P&L, Bilan
- Export FEC (France)
- Portail expert-comptable
- API REST publique

### Phase 3 : E-Invoicing & IA (4-6 mois)
- E-invoicing par pays
- OCR factures fournisseurs
- Suggestions IA comptes comptables
- Prédiction dates de paiement
- Détection anomalies
- Intégrations Stripe, GoCardless
- Open Banking PSD2
- Zapier / Make.com

## Contribution

Voir [CONTRIBUTING.md](CONTRIBUTING.md) pour les guidelines de contribution.

## Licence

Propriétaire - Tous droits réservés © 2025 ComptaPro SaaS

## Support

Pour toute question : support@comptapro.com

---

**Version**: 1.0.0
**Dernière mise à jour**: 18 novembre 2025
