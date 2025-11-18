# Guide de Démarrage Rapide - ComptaPro SaaS

## 🚀 Lancement en 5 minutes

### 1. Installation rapide

```bash
# Cloner et installer
git clone https://github.com/haythemsaa/comptapro-.git
cd comptapro-
composer install
npm install --legacy-peer-deps

# Configuration
cp .env.example .env
php artisan key:generate
```

### 2. Base de données (MySQL)

```bash
# Créer la BDD
mysql -u root -p -e "CREATE DATABASE comptapro CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Configurer .env
# DB_CONNECTION=mysql
# DB_DATABASE=comptapro
# DB_USERNAME=root
# DB_PASSWORD=votre_password

# Migrer et seeder
php artisan migrate --seed
```

### 3. Lancer l'application

```bash
# Terminal 1 - Backend
php artisan serve

# Terminal 2 - Frontend
npm run dev
```

Accédez à **http://localhost:8000**

## 🔐 Comptes de test

### Administrateur
- **Email:** admin@comptapro.com
- **Password:** password
- **Accès:** 2 sociétés (BE + FR)

### Utilisateur
- **Email:** jean@example.com
- **Password:** password
- **Accès:** 1 société (BE)

## 📊 Fonctionnalités disponibles

### ✅ Actuellement implémenté

1. **Dashboard multi-sociétés**
   - Statistiques en temps réel
   - Chiffre d'affaires par société
   - Indicateurs clients/factures
   - Sélecteur de société

2. **Gestion Clients**
   - CRUD complet
   - Numérotation automatique
   - Filtrage et recherche
   - Historique factures

3. **Facturation complète** ✨ NOUVEAU
   - Création devis/factures/avoirs (QT, INV, CN)
   - Numérotation automatique par type
   - Lignes de facture avec produits/services
   - Calcul TVA automatique
   - Multi-taux de TVA par ligne
   - Gestion statuts (draft, sent, paid, overdue)
   - Enregistrement des paiements
   - Recherche et filtrage avancés
   - **Interface complète** (Index, Create, Edit, Show) ✅

4. **Produits & Services** ✨ NOUVEAU
   - Catalogue complet avec SKU
   - Gestion stock (quantité, niveau minimum)
   - Prix unitaire et prix de revient
   - Taux de TVA par produit
   - Catégories
   - Activation/désactivation
   - **Interface complète** (Index, Create, Edit) ✅

5. **Gestion Sociétés** ✨ NOUVEAU
   - Création de sociétés multi-pays
   - Gestion des utilisateurs par société
   - Rôles (admin, accountant, user)
   - Protection du dernier admin
   - Plans d'abonnement
   - Changement de société en session

6. **Comptabilité complète** ✨ NOUVEAU
   - Plan comptable hiérarchique
   - 5 types de comptes (asset, liability, equity, revenue, expense)
   - Journaux comptables (ventes, achats, banque, caisse, général)
   - Écritures comptables en partie double
   - Validation équilibrage débit/crédit
   - Mise à jour automatique des soldes
   - Grand livre (General Ledger)
   - Balance générale (Trial Balance)

7. **Rapports financiers** ✨ NOUVEAU
   - Compte de Résultat (P&L)
   - Bilan (Balance Sheet)
   - Rapport TVA par taux
   - Balance âgée (Aged Receivables: current, 1-30, 31-60, 61-90, 90+)
   - Ventes par client
   - Relevé client
   - Cash Flow (simplifié)

8. **Architecture Multi-Pays**
   - 🇧🇪 Belgique : 21% TVA, PCN
   - 🇫🇷 France : 20% TVA, PCG 2025
   - 🇨🇭 Suisse : 8.1% TVA, Plan KMU
   - 🇹🇳 Tunisie : 19% TVA

9. **Authentification**
   - Login/Register
   - Email verification
   - Password reset
   - Profile management

10. **Base de données complète**
    - **21 tables migrées** (15 initiales + 6 nouvelles)
    - Relations optimisées
    - Multi-tenant ready
    - Soft deletes
    - Nouvelles tables:
      - suppliers (fournisseurs)
      - purchase_invoices (factures d'achat)
      - purchase_invoice_lines (lignes factures achat)
      - invoice_reminders (relances)
      - + champs paiement en ligne sur invoices

11. **Interface utilisateur** ✨ NOUVEAU
    - **Navigation Bootstrap 5** avec menu complet
    - **15+ vues Vue.js** créées (3,800+ lignes)
    - Factures: Index, Create, Edit, Show ✅
    - Produits: Index, Create, Edit ✅
    - Fournisseurs: Index ✅
    - Achats: Index ✅
    - Rapports: Index avec tous les liens ✅
    - Design responsive et moderne
    - Icônes Bootstrap Icons
    - Filtres et recherche en temps réel
    - Modales pour actions rapides

12. **Module Achats** 🆕 PENNYLANE-INSPIRED
    - Gestion complète des fournisseurs
    - Numérotation automatique (SUP-00001)
    - Factures d'achat avec workflow (draft → received → approved → paid)
    - Numérotation: PUR-00001, PCN-00001
    - **Upload factures avec OCR ready** (PDF/JPG/PNG)
    - Paiements partiels
    - Approbation avec audit trail
    - Référence fournisseur
    - Catégories (Biens/Services)
    - Coordonnées bancaires (IBAN/BIC)
    - Délais de paiement personnalisables

13. **Paiement en ligne** 🆕 PENNYLANE-INSPIRED
    - **Génération liens de paiement sécurisés**
    - Token unique 64 caractères
    - Expiration automatique (30 jours)
    - Page publique de paiement
    - Support Stripe/PayPal (ready)
    - Virement bancaire avec instructions
    - Paiements partiels
    - Tracking des paiements

14. **Relances automatiques** 🆕 PENNYLANE-INSPIRED
    - **Commande Artisan** : `php artisan invoices:send-reminders`
    - Workflow intelligent:
      - J+7 : 1ère relance (rappel poli)
      - J+15 : 2ème relance (demande ferme)
      - J+30 : Mise en demeure (avertissement juridique)
    - Détection automatique des retards
    - Évite les doublons
    - Tracking complet (envois, ouvertures)
    - Templates personnalisés par type
    - Ready pour cron job quotidien

15. **Balance détaillée N vs N-1** 🆕 PENNYLANE-INSPIRED
    - Comparaison année en cours vs année précédente
    - Balance par compte avec:
      - Solde année N
      - Solde année N-1
      - Écart absolu
      - Écart en pourcentage
    - Groupement par type de compte
    - Analyse d'évolution
    - Détection variations importantes

### 🚧 À développer (Roadmap)

1. **✅ Priority 1: Frontend complet** - TERMINÉ! 🎉
   - ✅ Interfaces pour factures (4 vues)
   - ✅ Interfaces pour produits (3 vues)
   - ✅ Interfaces pour sociétés (4 vues)
   - ✅ Interfaces pour comptabilité (1 vue)
   - ✅ Interfaces pour rapports (3 vues)
   - ✅ Navigation complète Bootstrap 5
   - **Total: 15+ vues Vue.js créées (2,900+ lignes)**

2. **Génération PDF** (Priority 2) - SUIVANT
   - Templates de factures professionnelles
   - Templates de rapports (P&L, Bilan, TVA)
   - Envoi par email avec attachements
   - Configuration des en-têtes/pieds de page

3. **Modules avancés** (Priority 3)
   - Module Achats complet
   - Module Bancaire avec rapprochement
   - Module Taxes avancé
   - Export FEC (France)
   - Gestion des immobilisations

## 🏗️ Structure du projet

```
comptapro-/
├── app/
│   ├── Http/Controllers/
│   │   ├── DashboardController.php ✅ (77 lignes)
│   │   ├── CustomerController.php ✅ (158 lignes)
│   │   ├── InvoiceController.php ✅ (355 lignes) ✨
│   │   ├── ProductController.php ✅ (162 lignes) ✨
│   │   ├── CompanyController.php ✅ (242 lignes) ✨
│   │   ├── AccountingController.php ✅ (405 lignes) ✨
│   │   └── ReportController.php ✅ (376 lignes) ✨
│   └── Models/Modules/
│       ├── Core/ (Country, Company) ✅
│       ├── Accounting/ (Account, Journal, JournalEntry) ✅
│       ├── Invoicing/ (Customer, Invoice, InvoiceLine) ✅
│       ├── Products/ (Product) ✅
│       ├── Tax/ ⚠️ (à développer)
│       ├── Banking/ ⚠️ (à développer)
│       └── Purchases/ ⚠️ (à développer)
├── database/
│   ├── migrations/ (15 tables) ✅
│   └── seeders/ (4 countries, 2 companies) ✅
├── routes/
│   └── web.php ✅ (40+ routes configurées)
└── resources/js/
    ├── Layouts/
    │   └── AuthenticatedLayout.vue ✅ ✨ (Navigation Bootstrap 5)
    └── Pages/
        ├── Dashboard.vue ✅ (Bootstrap 5)
        ├── Customers/Index.vue ✅
        ├── Invoices/ ✅ ✨
        │   ├── Index.vue (Liste + filtres + pagination)
        │   ├── Create.vue (Formulaire multi-lignes + calculs)
        │   ├── Edit.vue (Modification brouillons)
        │   └── Show.vue (Détails + actions + paiement modal)
        ├── Products/ ✅ ✨
        │   ├── Index.vue (Catalogue + gestion stock)
        │   ├── Create.vue (Formulaire complet)
        │   └── Edit.vue (Modification)
        ├── Reports/ ✅ ✨
        │   └── Index.vue (Dashboard des rapports)
        ├── Companies/ ⚠️ (à créer: Index, Create, Edit, Show)
        └── Accounting/ ⚠️ (à créer: Accounts, Journals, Entries)

✅ Complété | ✨ Nouveau | ⚠️ À développer
```

## 🎨 Technologies

- **Backend:** Laravel 11, PHP 8.2+
- **Frontend:** Vue 3, Inertia.js, Bootstrap 5
- **Database:** MySQL/PostgreSQL
- **Auth:** Laravel Sanctum
- **Permissions:** Spatie Laravel Permission

## 📝 Workflow de développement

### Développer un nouveau module

```bash
# 1. Créer le controller
php artisan make:controller ModuleController

# 2. Créer les routes
# Ajouter dans routes/web.php
Route::resource('modules', ModuleController::class);

# 3. Créer les vues Vue.js
# resources/js/Pages/Modules/*.vue

# 4. Tester
php artisan test
```

### Ajouter un pays

```php
// database/seeders/CountrySeeder.php
[
    'code' => 'XX',
    'name' => 'Nouveau Pays',
    'currency' => 'XXX',
    'default_vat_rate' => 20.00,
    'accounting_plan' => 'Plan local',
    'vat_rates' => json_encode([20, 10, 5]),
    // ...
]
```

## 🐛 Debugging

```bash
# Logs Laravel
tail -f storage/logs/laravel.log

# Cache clear
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# Rebuild frontend
npm run build

# Reset database
php artisan migrate:fresh --seed
```

## 📚 Documentation complète

- [README.md](README.md) - Vue d'ensemble
- [INSTALLATION.md](INSTALLATION.md) - Installation détaillée
- [docs/ARCHITECTURE.md](docs/ARCHITECTURE.md) - Architecture technique
- [Cahier des charges](Cahier_Specifications_ComptaPro_SaaS.docx) - Spécifications

## 🤝 Contribution

1. Cloner la branche
2. Créer une feature branch
3. Commit avec messages clairs
4. Push et créer une PR
5. Tests passent ✅

## 📧 Support

- **Issues:** https://github.com/haythemsaa/comptapro-/issues
- **Email:** support@comptapro.com

---

**Version:** 1.0.0 (MVP)
**Status:** En développement actif
**Dernière mise à jour:** 18 novembre 2025
