# ComptaPro SaaS

**Application de Comptabilité & Facturation Multi-Pays**

[![Laravel](https://img.shields.io/badge/Laravel-11+-FF2D20?style=flat&logo=laravel)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=flat&logo=php)](https://php.net)
[![Vue.js](https://img.shields.io/badge/Vue.js-3-4FC08D?style=flat&logo=vue.js)](https://vuejs.org)
[![Bootstrap](https://img.shields.io/badge/Bootstrap-5-7952B3?style=flat&logo=bootstrap)](https://getbootstrap.com)

## 🌍 À propos

ComptaPro SaaS est une solution de comptabilité et facturation en ligne conçue pour les PME/TPE opérant en Belgique, France, Suisse et Tunisie. L'application combine la simplicité d'utilisation, la puissance des fonctionnalités et l'adaptation automatique aux réglementations fiscales de chaque pays.

### 🎯 Caractéristiques principales

- ✅ **Multi-pays natif** : Adaptation automatique aux règles fiscales (BE, FR, CH, TN)
- ✅ **UX guidée** : Utilisable sans diplôme comptable
- ✅ **Prêt pour l'e-invoicing** : Peppol, Chorus Pro, TTN/El Fatoora
- ✅ **Automatisation intelligente** : Rapprochement bancaire, suggestions TVA, alertes
- ✅ **Multi-sociétés** : Gérez plusieurs entreprises dans un seul compte
- ✅ **Multi-utilisateurs** : Rôles granulaires et permissions avancées

## 📦 Modules

| Module | Description |
|--------|-------------|
| **Core** | Gestion sociétés, utilisateurs, paramètres globaux |
| **Accounting** | Plan comptable, journaux, écritures, clôtures |
| **Invoicing** | Devis, factures, avoirs, relances, paiements |
| **Tax** | TVA, déclarations fiscales, exports XML |
| **Banking** | Comptes bancaires, imports relevés, rapprochement |
| **Purchases** | Fournisseurs, commandes, factures achats |
| **Products** | Produits/services, stock, catégories |
| **Reporting** | Dashboards, rapports financiers, analytics |

## 🚀 Installation

### Prérequis

- PHP 8.2 ou supérieur
- Composer 2+
- Node.js 18+
- PostgreSQL 14+ ou MySQL 8.0+
- Redis 6+

### Installation locale

```bash
# Cloner le repository
git clone https://github.com/haythemsaa/comptapro-.git
cd comptapro-

# Installer les dépendances PHP
composer install

# Installer les dépendances JavaScript
npm install

# Configuration de l'environnement
cp .env.example .env
php artisan key:generate

# Configurer la base de données dans .env
# Puis exécuter les migrations
php artisan migrate --seed

# Compiler les assets
npm run dev

# Démarrer le serveur de développement
php artisan serve
```

L'application sera accessible sur `http://localhost:8000`

## 🌐 Pays supportés

| Pays | Devise | TVA | Plan Comptable | E-invoicing |
|------|--------|-----|----------------|-------------|
| 🇧🇪 **Belgique** | EUR | 21% (+ 12%, 6%, 0%) | PCN | Peppol B2B 2026 |
| 🇫🇷 **France** | EUR | 20% (+ 10%, 5.5%, 2.1%) | PCG 2025 | Chorus Pro |
| 🇨🇭 **Suisse** | CHF | 8.1% (+ 2.6%, 3.8%) | Plan KMU | Non obligatoire |
| 🇹🇳 **Tunisie** | TND | 19% (+ 13%, 7%, 0%) | Plan tunisien | TTN / El Fatoora |

## 👥 Profils utilisateurs

1. **Super Admin SaaS** - Gestion complète de la plateforme
2. **Admin Société** - Gestion complète d'une société
3. **Responsable Commercial** - Clients, devis, factures
4. **Gestionnaire Achats** - Fournisseurs, commandes, factures achats
5. **Comptable / Teneur de Livres** - Écritures, TVA, rapprochement
6. **Expert-Comptable Externe** - Lecture + ajustements de clôture

## 💰 Tarification

| Plan | Prix | Sociétés | Utilisateurs | Factures/an |
|------|------|----------|--------------|-------------|
| **Starter** | 29 €/mois | 1 | 2 | 500 |
| **Professional** | 79 €/mois | 3 | 5 | Illimité |
| **Enterprise** | Sur devis | Illimité | Illimité | Illimité |

## 🔒 Sécurité

- Authentification Laravel Sanctum avec 2FA optionnel
- Système de permissions granulaire (Spatie)
- Protection CSRF/XSS native Laravel
- Chiffrement des données sensibles
- Audit trail complet
- Conformité RGPD

## 📚 Documentation

- [Architecture](docs/ARCHITECTURE.md)
- [Cahier des charges](Cahier_Specifications_ComptaPro_SaaS.docx)
- API Documentation (à venir)
- Guide utilisateur (à venir)

## 🛣️ Roadmap

### ✅ Phase 1 : MVP (En cours)
- Authentification & gestion utilisateurs
- Paramétrage multi-pays (BE, FR)
- Plan comptable pré-configuré
- Clients, fournisseurs, produits
- Facturation complète (devis, factures, avoirs)
- Écritures comptables
- Synthèse TVA simple
- Dashboard basique
- Rapports : Grand livre, Balance

### 🔄 Phase 2 : Consolidation (T1 2026)
- Extension Suisse & Tunisie
- Import relevés bancaires
- Rapprochement bancaire semi-automatique
- Factures fournisseurs complètes
- Déclarations TVA avec export XML
- Clôtures périodiques
- Gestion stock simple
- Rapports avancés : P&L, Bilan
- Export FEC (France)
- Portail expert-comptable
- API REST publique

### 🚀 Phase 3 : E-Invoicing & IA (T2-T3 2026)
- E-invoicing par pays (Peppol, Chorus Pro, TTN)
- OCR factures fournisseurs
- Suggestions IA pour comptes comptables
- Prédiction dates de paiement
- Détection anomalies comptables
- Intégrations Stripe, GoCardless
- Open Banking PSD2
- Zapier / Make.com

## 🧪 Tests

```bash
# Tests unitaires
php artisan test --testsuite=Unit

# Tests fonctionnels
php artisan test --testsuite=Feature

# Tests avec couverture
php artisan test --coverage

# Analyse statique
./vendor/bin/phpstan analyse
```

## 🤝 Contribution

Les contributions sont les bienvenues ! Consultez [CONTRIBUTING.md](CONTRIBUTING.md) pour plus d'informations.

## 📄 Licence

Propriétaire - Tous droits réservés © 2025 ComptaPro SaaS

## 📧 Support

Pour toute question ou support :
- Email : support@comptapro.com
- Documentation : https://docs.comptapro.com

---

**Développé avec ❤️ pour les entrepreneurs et PME**

**Version**: 1.0.0 (MVP en développement)
**Dernière mise à jour**: 18 novembre 2025
