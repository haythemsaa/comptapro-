# Guide d'Installation - ComptaPro SaaS

## Prérequis

- PHP 8.2 ou supérieur
- Composer 2+
- Node.js 18+ et NPM
- MySQL 8.0+ ou PostgreSQL 14+
- Redis 6+ (optionnel mais recommandé)

## Installation pas à pas

### 1. Cloner le repository

```bash
git clone https://github.com/haythemsaa/comptapro-.git
cd comptapro-
```

### 2. Installer les dépendances PHP

```bash
composer install
```

### 3. Installer les dépendances JavaScript

```bash
npm install --legacy-peer-deps
```

### 4. Configuration de l'environnement

```bash
cp .env.example .env
php artisan key:generate
```

### 5. Configurer la base de données

Éditez le fichier `.env` et configurez votre base de données :

#### Pour MySQL :
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=comptapro
DB_USERNAME=root
DB_PASSWORD=votre_mot_de_passe
```

#### Pour PostgreSQL :
```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=comptapro
DB_USERNAME=postgres
DB_PASSWORD=votre_mot_de_passe
```

### 6. Créer la base de données

#### MySQL :
```bash
mysql -u root -p -e "CREATE DATABASE comptapro CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

#### PostgreSQL :
```bash
createdb -U postgres comptapro
```

### 7. Exécuter les migrations et seeders

```bash
php artisan migrate --seed
```

Cette commande va :
- Créer toutes les tables de la base de données
- Insérer les 4 pays (Belgique, France, Suisse, Tunisie)
- Créer 2 utilisateurs de test
- Créer 2 sociétés de démonstration
- Créer quelques clients de test

### 8. Compiler les assets

#### Pour le développement :
```bash
npm run dev
```

#### Pour la production :
```bash
npm run build
```

### 9. Démarrer le serveur de développement

```bash
php artisan serve
```

L'application sera accessible sur `http://localhost:8000`

## Comptes de test

Après l'exécution du seeder, vous pouvez vous connecter avec :

### Compte Administrateur
- **Email** : admin@comptapro.com
- **Mot de passe** : password
- **Accès** : 2 sociétés (Belgique et France)

### Compte Utilisateur
- **Email** : jean@example.com
- **Mot de passe** : password
- **Accès** : 1 société (Belgique) avec rôle Comptable

## Configuration supplémentaire

### Redis (optionnel)

Pour améliorer les performances, configurez Redis pour le cache et les queues :

```env
CACHE_STORE=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

### Mail

Configurez l'envoi d'emails :

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=votre@email.com
MAIL_PASSWORD=votre_mot_de_passe
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=votre@email.com
MAIL_FROM_NAME="ComptaPro SaaS"
```

## Structure de la base de données

### Tables principales

1. **countries** - Pays supportés (BE, FR, CH, TN)
2. **companies** - Sociétés multi-tenants
3. **users** - Utilisateurs de l'application
4. **company_user** - Relations utilisateurs-sociétés avec rôles
5. **accounts** - Plan comptable par société
6. **journals** - Journaux comptables
7. **journal_entries** - Écritures comptables
8. **journal_entry_lines** - Lignes d'écritures (débit/crédit)
9. **customers** - Clients
10. **suppliers** - Fournisseurs
11. **products** - Produits et services
12. **invoices** - Factures (devis, factures, avoirs)
13. **invoice_lines** - Lignes de factures
14. **payments** - Paiements clients
15. **tax_rates** - Taux de TVA
16. **bank_accounts** - Comptes bancaires

## Commandes utiles

### Réinitialiser la base de données
```bash
php artisan migrate:fresh --seed
```

### Vider le cache
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Exécuter les tests
```bash
php artisan test
```

### Lancer les queues (si configuré)
```bash
php artisan queue:work
```

## Dépannage

### Erreur de permissions sur storage/
```bash
chmod -R 775 storage bootstrap/cache
```

### Problème avec les dépendances npm
```bash
rm -rf node_modules package-lock.json
npm install --legacy-peer-deps
```

### Erreur de connexion à la base de données
- Vérifiez que MySQL/PostgreSQL est démarré
- Vérifiez les identifiants dans `.env`
- Vérifiez que la base de données existe

## Support

Pour toute question ou problème :
- GitHub Issues : https://github.com/haythemsaa/comptapro-/issues
- Email : support@comptapro.com

## Licence

Propriétaire - © 2025 ComptaPro SaaS
