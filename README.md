# 💼 ComptaPro SaaS - Modern Accounting Application

![ComptaPro](https://img.shields.io/badge/ComptaPro-v1.0.0-blue)
![Laravel](https://img.shields.io/badge/Laravel-11-red)
![Vue.js](https://img.shields.io/badge/Vue.js-3-green)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5-purple)
![License](https://img.shields.io/badge/License-MIT-yellow)

**ComptaPro** est une application SaaS de comptabilité moderne et complète, construite avec Laravel 11 et Vue.js 3. Elle offre une gestion complète de la comptabilité multi-pays avec un design élégant et des fonctionnalités avancées inspirées de Pennylane.

---

## ✨ Fonctionnalités Principales

### 📊 **Comptabilité Complète**
- ✅ **Multi-pays** : France, Belgique, Luxembourg, Suisse, Canada
- ✅ **Plan comptable** : PCG, PCMN, PCN-Lux, PCN-CH, Québec
- ✅ **Multi-devise** : EUR, CHF, CAD
- ✅ **Journaux comptables** : Ventes, Achats, Banque, OD
- ✅ **Écritures comptables** : Saisie et validation
- ✅ **Grand livre** : Consultation par compte
- ✅ **Balance** : Génération automatique

### 💰 **Facturation & Ventes**
- ✅ **Clients** : Gestion complète avec auto-numérotation
- ✅ **Factures & Devis** : Création, envoi, suivi
- ✅ **Avoirs** : Génération automatique
- ✅ **Produits & Services** : Catalogue avec TVA
- ✅ **PDF professionnel** : Génération automatique
- ✅ **Envoi email** : Avec pièce jointe PDF
- ✅ **Liens de paiement** : Stripe/PayPal ready
- ✅ **Rappels automatiques** : 3 niveaux (J+7, J+15, J+30)

### 🛒 **Achats**
- ✅ **Fournisseurs** : Gestion complète
- ✅ **Factures d'achat** : Avec workflow (Reçu → Approuvé → Payé)
- ✅ **OCR ready** : Prêt pour Tesseract/Google Vision/AWS Textract
- ✅ **Paiements** : Suivi et enregistrement

### 📈 **Rapports**
- ✅ **Compte de résultat** : Revenus vs Dépenses
- ✅ **Bilan** : Actif/Passif avec vérification équilibre
- ✅ **TVA** : Rapport avec taux détaillés
- ✅ **Balance détaillée** : Comparaison N vs N-1
- ✅ **Flux de trésorerie** : Suivi en temps réel
- ✅ **Export PDF** : Tous les rapports

### 🎨 **Design Moderne**
- ✅ **Sidebar animée** : Collapsible avec animations fluides
- ✅ **Dashboard visuel** : Stats, charts, graphiques
- ✅ **Dark mode** : Support complet
- ✅ **Responsive** : Mobile, Tablet, Desktop
- ✅ **50+ animations CSS** : Transitions professionnelles
- ✅ **Composants UI** : Modal, Alert, Card, Loading
- ✅ **Notifications** : Système de toast intégré

### 🏢 **Multi-tenant**
- ✅ **Sociétés multiples** : Un utilisateur, plusieurs sociétés
- ✅ **Isolation des données** : Par société
- ✅ **Plans d'abonnement** : Basic, Pro, Enterprise
- ✅ **Switch facile** : Entre sociétés

---

## 🚀 Technologies Utilisées

### **Backend**
- **Laravel 11** : Framework PHP moderne
- **MySQL/PostgreSQL** : Base de données
- **Inertia.js** : SPA sans API
- **DomPDF** : Génération de PDF
- **Laravel Mail** : Envoi d'emails

### **Frontend**
- **Vue.js 3** : Composition API
- **Bootstrap 5** : Framework CSS
- **Vite** : Build tool rapide
- **Bootstrap Icons** : Icônes modernes

### **Features**
- **Authentication** : Laravel Breeze
- **Multi-tenant** : Par société
- **PDF Generation** : Factures, Rapports
- **Email System** : Mailables avec templates
- **Payment Links** : Secure tokens
- **Auto Reminders** : Artisan command

---

## 📦 Installation

### **Prérequis**
- PHP 8.2+
- Composer
- Node.js 18+
- MySQL 8.0+ ou PostgreSQL 14+

### **Installation Rapide**

```bash
# 1. Cloner le repository
git clone https://github.com/votre-username/comptapro.git
cd comptapro

# 2. Installer les dépendances
composer install
npm install

# 3. Configuration
cp .env.example .env
php artisan key:generate

# 4. Base de données
# Configurer .env avec vos credentials
php artisan migrate --seed

# 5. Build assets
npm run build

# 6. Lancer le serveur
php artisan serve
```

Accédez à : `http://localhost:8000`

---

## 🔧 Configuration

### **Variables d'environnement (.env)**

```env
# Application
APP_NAME="ComptaPro SaaS"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://votre-domaine.com

# Base de données
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=comptapro
DB_USERNAME=root
DB_PASSWORD=

# Mail (pour envoi factures)
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your-username
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@comptapro.com
MAIL_FROM_NAME="${APP_NAME}"

# Paiements (optionnel)
STRIPE_KEY=pk_test_xxxxx
STRIPE_SECRET=sk_test_xxxxx
PAYPAL_CLIENT_ID=xxxxx
PAYPAL_SECRET=xxxxx
```

### **Tâches Cron**

Ajouter dans crontab pour les rappels automatiques :

```bash
* * * * * cd /path/to/comptapro && php artisan schedule:run >> /dev/null 2>&1
```

Dans `app/Console/Kernel.php` :

```php
protected function schedule(Schedule $schedule)
{
    $schedule->command('invoices:send-reminders')->daily();
}
```

---

## 📚 Structure du Projet

```
comptapro/
├── app/
│   ├── Console/Commands/
│   │   └── SendInvoiceReminders.php
│   ├── Http/Controllers/
│   │   ├── CustomerController.php
│   │   ├── InvoiceController.php
│   │   ├── ProductController.php
│   │   ├── SupplierController.php
│   │   ├── PurchaseController.php
│   │   ├── PaymentController.php
│   │   ├── ReportController.php
│   │   └── ...
│   ├── Mail/
│   │   ├── InvoiceMail.php
│   │   └── InvoiceReminderMail.php
│   ├── Models/
│   │   ├── Company.php
│   │   ├── Customer.php
│   │   ├── Invoice.php
│   │   ├── Product.php
│   │   ├── Supplier.php
│   │   ├── PurchaseInvoice.php
│   │   ├── Account.php
│   │   └── ...
│   └── Services/
│       └── PdfGenerator.php
├── database/
│   ├── migrations/
│   └── seeders/
├── resources/
│   ├── css/
│   │   ├── app.css
│   │   └── animations.css
│   ├── js/
│   │   ├── Components/
│   │   │   ├── Modal.vue
│   │   │   ├── Alert.vue
│   │   │   ├── Card.vue
│   │   │   └── Loading.vue
│   │   ├── Layouts/
│   │   │   └── AuthenticatedLayout.vue
│   │   └── Pages/
│   │       ├── Dashboard.vue
│   │       ├── Customers/
│   │       ├── Invoices/
│   │       ├── Products/
│   │       ├── Suppliers/
│   │       ├── Purchases/
│   │       └── ...
│   └── views/
│       ├── emails/
│       │   ├── invoice.blade.php
│       │   └── invoice-reminder.blade.php
│       └── pdf/
│           ├── invoice.blade.php
│           └── reports/
│               ├── profit-loss.blade.php
│               ├── balance-sheet.blade.php
│               └── vat-report.blade.php
├── routes/
│   └── web.php
├── DESIGN_GUIDE.md
├── QUICK_START.md
└── README.md
```

---

## 🎨 Design System

ComptaPro utilise un système de design moderne et cohérent :

### **Couleurs**
- **Primary** : #3b82f6 (Bleu)
- **Success** : #10b981 (Vert)
- **Warning** : #f59e0b (Orange)
- **Danger** : #ef4444 (Rouge)

### **Composants UI**
- **Modal** : 4 tailles (sm, md, lg, xl)
- **Alert** : 4 variants (success, info, warning, danger)
- **Card** : 4 variants (default, gradient, bordered, elevated)
- **Loading** : 4 types (spinner, dots, pulse, bars)

### **Animations**
- 50+ animations CSS
- Transitions fluides
- Hover effects
- Stagger animations

Voir [DESIGN_GUIDE.md](./DESIGN_GUIDE.md) pour plus de détails.

---

## 📖 Documentation

- **[QUICK_START.md](./QUICK_START.md)** : Guide de démarrage rapide
- **[DESIGN_GUIDE.md](./DESIGN_GUIDE.md)** : Système de design complet
- **[PENNYLANE_FEATURES.md](./PENNYLANE_FEATURES.md)** : Features inspirées de Pennylane

---

## 🔐 Sécurité

### **Authentification**
- Laravel Breeze (session-based)
- CSRF protection
- Password hashing (bcrypt)

### **Paiements**
- Tokens sécurisés (64 chars, random_bytes)
- Expiration (30 jours)
- Validation stricte

### **Multi-tenant**
- Isolation par société (company_id)
- Middleware de vérification
- Pas de data leakage

---

## 🧪 Tests

```bash
# Tests unitaires
php artisan test

# Tests feature
php artisan test --filter=InvoiceTest

# Coverage
php artisan test --coverage
```

---

## 📊 Base de Données

### **Tables Principales**

- **companies** : Sociétés
- **users** : Utilisateurs
- **customers** : Clients (CUS-00001)
- **invoices** : Factures (INV-00001, QUO-00001, CRN-00001)
- **invoice_lines** : Lignes de factures
- **products** : Produits & Services (PRD-00001)
- **suppliers** : Fournisseurs (SUP-00001)
- **purchase_invoices** : Factures d'achat (PUR-00001)
- **accounts** : Plan comptable
- **journal_entries** : Écritures comptables
- **invoice_reminders** : Rappels automatiques

**Total** : 21 tables

---

## 🚀 Déploiement

### **Production Checklist**

- [ ] Configurer `.env` pour production
- [ ] `APP_DEBUG=false`
- [ ] Configurer base de données production
- [ ] Configurer SMTP pour emails
- [ ] Générer `APP_KEY`
- [ ] Optimiser : `php artisan optimize`
- [ ] Cache config : `php artisan config:cache`
- [ ] Cache routes : `php artisan route:cache`
- [ ] Build assets : `npm run build`
- [ ] Configurer SSL/HTTPS
- [ ] Configurer cron pour rappels
- [ ] Backups automatiques DB
- [ ] Monitoring (Sentry, Bugsnag)

### **Performance**

```bash
# Optimisation Laravel
php artisan optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Build production
npm run build
```

---

## 🤝 Contribution

Les contributions sont les bienvenues !

1. Fork le projet
2. Créer une branche (`git checkout -b feature/AmazingFeature`)
3. Commit (`git commit -m 'Add AmazingFeature'`)
4. Push (`git push origin feature/AmazingFeature`)
5. Ouvrir une Pull Request

---

## 📝 License

Ce projet est sous licence MIT. Voir `LICENSE` pour plus d'informations.

---

## 👥 Auteurs

- **Votre Nom** - *Développement initial* - [@votre-github](https://github.com/votre-username)

---

## 🙏 Remerciements

- Laravel Framework
- Vue.js Team
- Bootstrap Team
- Pennylane (inspiration)
- Tous les contributeurs

---

## 📧 Contact

- **Email** : contact@comptapro.com
- **Website** : https://comptapro.com
- **Support** : support@comptapro.com

---

## 🗺️ Roadmap

### **Version 1.1** (Q2 2025)
- [ ] Import bancaire (OFX, CSV)
- [ ] Rapprochement bancaire
- [ ] Multi-utilisateurs par société
- [ ] Rôles et permissions
- [ ] API REST complète
- [ ] Mobile app (iOS/Android)

### **Version 1.2** (Q3 2025)
- [ ] Inventaire avancé
- [ ] Gestion des stocks
- [ ] Bons de commande
- [ ] Bons de livraison
- [ ] Devis estimatifs
- [ ] Contrats récurrents

### **Version 2.0** (Q4 2025)
- [ ] IA pour catégorisation automatique
- [ ] OCR avancé pour factures
- [ ] Prévisions de trésorerie
- [ ] Dashboard personnalisable
- [ ] Widgets drag & drop
- [ ] Intégrations tierces (Stripe, PayPal, etc.)

---

## 📊 Statistiques

- **21 tables** de base de données
- **15+ vues Vue.js** complètes
- **50+ animations** CSS
- **4 composants** UI réutilisables
- **3 templates email** professionnels
- **4 templates PDF** (factures + rapports)
- **5 pays** supportés
- **3 devises** supportées
- **100% responsive**
- **Dark mode** complet

---

## 🎯 Features Clés

### **✅ Inspiré de Pennylane**
- Module Achats complet avec OCR
- Liens de paiement en ligne
- Rappels automatiques (3 niveaux)
- Balance détaillée N vs N-1

### **✅ Design Moderne**
- Sidebar collapsible animée
- Dashboard avec charts visuels
- 50+ animations fluides
- Dark mode support

### **✅ Productivité**
- Auto-numérotation (CUS-00001, INV-00001, etc.)
- Templates email professionnels
- PDF automatiques
- Rappels automatisés

### **✅ Multi-pays**
- Plans comptables : PCG, PCMN, PCN-Lux, PCN-CH, Québec
- Devises : EUR, CHF, CAD
- TVA par pays
- Formats de numérotation locaux

---

**ComptaPro SaaS - La comptabilité moderne et élégante** 💼✨

Made with ❤️ by the ComptaPro Team
