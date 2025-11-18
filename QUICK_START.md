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

3. **Architecture Multi-Pays**
   - 🇧🇪 Belgique : 21% TVA, PCN
   - 🇫🇷 France : 20% TVA, PCG 2025
   - 🇨🇭 Suisse : 8.1% TVA, Plan KMU
   - 🇹🇳 Tunisie : 19% TVA

4. **Authentification**
   - Login/Register
   - Email verification
   - Password reset
   - Profile management

5. **Base de données complète**
   - 15 tables migrées
   - Relations optimisées
   - Multi-tenant ready
   - Soft deletes

### 🚧 À développer (Roadmap)

1. **Facturation** (Priority 1)
   - Création de devis/factures
   - Génération PDF
   - Envoi email
   - Gestion paiements
   - Calcul TVA automatique

2. **Comptabilité** (Priority 2)
   - Plan comptable pré-configuré
   - Saisie écritures
   - Journaux comptables
   - Grand livre, Balance

3. **Produits & Services** (Priority 3)
   - Catalogue produits
   - Gestion stock
   - Prix par pays
   - Catégories

4. **Reports & Analytics** (Priority 4)
   - P&L, Bilan
   - Export FEC (France)
   - Déclarations TVA
   - Rapports personnalisés

## 🏗️ Structure du projet

```
comptapro-/
├── app/
│   ├── Http/Controllers/
│   │   ├── DashboardController.php ✅
│   │   ├── CustomerController.php ✅
│   │   ├── InvoiceController.php ⚠️
│   │   ├── ProductController.php ⚠️
│   │   └── CompanyController.php ⚠️
│   └── Models/Modules/
│       ├── Core/ (Country, Company) ✅
│       ├── Accounting/ (Account, Journal) ✅
│       ├── Invoicing/ (Customer, Invoice) ✅
│       ├── Tax/ ⚠️
│       ├── Banking/ ⚠️
│       ├── Purchases/ ⚠️
│       └── Products/ ⚠️
├── database/
│   ├── migrations/ (15 tables) ✅
│   └── seeders/ (4 countries, 2 companies) ✅
└── resources/js/Pages/
    └── Dashboard.vue ✅ (Bootstrap 5)

✅ Complété | ⚠️ À développer
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
