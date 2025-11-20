# Guide de Déploiement ComptaPro Tunisia

Guide complet pour déployer ComptaPro Tunisia en production.

## Table des Matières

1. [Prérequis](#prérequis)
2. [Installation avec Docker](#installation-avec-docker)
3. [Installation Manuelle](#installation-manuelle)
4. [Configuration](#configuration)
5. [Migration et Seeders](#migration-et-seeders)
6. [Sécurité](#sécurité)
7. [Optimisation](#optimisation)
8. [Sauvegarde](#sauvegarde)
9. [Maintenance](#maintenance)
10. [Dépannage](#dépannage)

---

## Prérequis

### Serveur Requis

- **OS**: Ubuntu 22.04 LTS (recommandé) ou Debian 11+
- **CPU**: Minimum 2 cores (4 cores recommandé pour production)
- **RAM**: Minimum 4GB (8GB recommandé pour production)
- **Stockage**: Minimum 50GB SSD
- **Connexion**: Bande passante stable pour API El Fatoora

### Logiciels Requis

**Avec Docker (Recommandé)**:
- Docker Engine 20.10+
- Docker Compose 2.0+

**Sans Docker**:
- PHP 8.2+
- PostgreSQL 15+
- Redis 7+
- Nginx 1.22+
- Composer 2.5+
- Node.js 18+ et NPM 9+

---

## Installation avec Docker

### 1. Cloner le Projet

```bash
git clone https://github.com/votre-repo/comptapro-tunisia.git
cd comptapro-tunisia
```

### 2. Configuration de l'Environnement

```bash
# Copier le fichier d'environnement
cp .env.example .env

# Éditer les variables d'environnement
nano .env
```

**Variables critiques à configurer**:

```env
# Application
APP_NAME="ComptaPro Tunisia"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://votre-domaine.tn

# Base de données
DB_CONNECTION=pgsql
DB_HOST=db
DB_PORT=5432
DB_DATABASE=comptapro
DB_USERNAME=comptapro
DB_PASSWORD=VotreMotDePasseSecurise123!

# Redis
REDIS_HOST=redis
REDIS_PASSWORD=VotreMotDePasseRedisSecurise123!
REDIS_PORT=6379

# Queue
QUEUE_CONNECTION=redis

# Cache
CACHE_DRIVER=redis
SESSION_DRIVER=redis

# El Fatoora (Configuration Tunisie)
ELFATOORA_API_URL=https://api.elfatoora.gov.tn
ELFATOORA_API_KEY=votre_cle_api_elfatoora
ELFATOORA_ENVIRONMENT=production

# IA Services
ANTHROPIC_API_KEY=votre_cle_anthropic
GOOGLE_CLOUD_VISION_KEY=votre_cle_google_vision

# Email
MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=votre_email
MAIL_PASSWORD=votre_mot_de_passe
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@votre-domaine.tn
MAIL_FROM_NAME="${APP_NAME}"
```

### 3. Construire et Démarrer les Conteneurs

```bash
# Construire les images
docker-compose build

# Démarrer les services
docker-compose up -d

# Vérifier que tous les services sont démarrés
docker-compose ps
```

### 4. Installation des Dépendances

```bash
# Entrer dans le conteneur
docker-compose exec app bash

# Installer les dépendances PHP
composer install --optimize-autoloader --no-dev

# Générer la clé d'application
php artisan key:generate

# Installer les dépendances Node.js et construire les assets
npm install
npm run build

# Sortir du conteneur
exit
```

### 5. Migration et Seeders

```bash
# Exécuter les migrations
docker-compose exec app php artisan migrate --force

# Seeder le Plan Comptable Normalisé (PCN)
docker-compose exec app php artisan db:seed --class=TunisianChartOfAccountsSeeder

# (Optionnel) Seeder des données de test pour développement
docker-compose exec app php artisan db:seed --class=TunisiaTestDataSeeder

# Optimisations
docker-compose exec app php artisan config:cache
docker-compose exec app php artisan route:cache
docker-compose exec app php artisan view:cache
```

### 6. Permissions

```bash
# Donner les bonnes permissions
docker-compose exec app chown -R www:www /var/www/storage
docker-compose exec app chown -R www:www /var/www/bootstrap/cache
docker-compose exec app chmod -R 775 /var/www/storage
docker-compose exec app chmod -R 775 /var/www/bootstrap/cache
```

---

## Installation Manuelle

### 1. Installation de PHP 8.2

```bash
sudo apt update
sudo apt install -y software-properties-common
sudo add-apt-repository ppa:ondrej/php
sudo apt update
sudo apt install -y php8.2 php8.2-fpm php8.2-cli php8.2-common php8.2-pgsql \
    php8.2-mbstring php8.2-xml php8.2-curl php8.2-zip php8.2-gd \
    php8.2-redis php8.2-bcmath php8.2-intl php8.2-soap
```

### 2. Installation de PostgreSQL 15

```bash
sudo sh -c 'echo "deb http://apt.postgresql.org/pub/repos/apt $(lsb_release -cs)-pgdg main" > /etc/apt/sources.list.d/pgdg.list'
wget --quiet -O - https://www.postgresql.org/media/keys/ACCC4CF8.asc | sudo apt-key add -
sudo apt update
sudo apt install -y postgresql-15

# Créer la base de données
sudo -u postgres psql
CREATE DATABASE comptapro;
CREATE USER comptapro WITH ENCRYPTED PASSWORD 'VotreMotDePasse';
GRANT ALL PRIVILEGES ON DATABASE comptapro TO comptapro;
\q
```

### 3. Installation de Redis

```bash
sudo apt install -y redis-server
sudo systemctl enable redis-server
sudo systemctl start redis-server

# Configurer le mot de passe Redis
sudo nano /etc/redis/redis.conf
# Décommenter et modifier: requirepass VotreMotDePasseRedis
sudo systemctl restart redis-server
```

### 4. Installation de Nginx

```bash
sudo apt install -y nginx

# Configuration Nginx
sudo nano /etc/nginx/sites-available/comptapro
```

**Configuration Nginx**:

```nginx
server {
    listen 80;
    server_name votre-domaine.tn;
    root /var/www/comptapro/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

```bash
# Activer le site
sudo ln -s /etc/nginx/sites-available/comptapro /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### 5. Installation de Composer

```bash
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

### 6. Déploiement de l'Application

```bash
cd /var/www
sudo git clone https://github.com/votre-repo/comptapro-tunisia.git comptapro
cd comptapro

# Installation des dépendances
composer install --optimize-autoloader --no-dev
npm install && npm run build

# Configuration
cp .env.example .env
nano .env  # Éditer les variables d'environnement

php artisan key:generate
php artisan migrate --force
php artisan db:seed --class=TunisianChartOfAccountsSeeder

# Optimisations
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Permissions
sudo chown -R www-data:www-data /var/www/comptapro
sudo chmod -R 775 /var/www/comptapro/storage
sudo chmod -R 775 /var/www/comptapro/bootstrap/cache
```

### 7. Configuration des Workers et Scheduler

**Queue Worker (Supervisor)**:

```bash
sudo apt install -y supervisor

# Créer la configuration
sudo nano /etc/supervisor/conf.d/comptapro-worker.conf
```

```ini
[program:comptapro-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/comptapro/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=4
redirect_stderr=true
stdout_logfile=/var/www/comptapro/storage/logs/worker.log
stopwaitsecs=3600
```

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start comptapro-worker:*
```

**Scheduler (Cron)**:

```bash
sudo crontab -e -u www-data
```

Ajouter:
```
* * * * * cd /var/www/comptapro && php artisan schedule:run >> /dev/null 2>&1
```

---

## Configuration

### Configuration El Fatoora

1. **Obtenir les certificats auprès de l'autorité tunisienne**
2. **Placer les certificats**:

```bash
mkdir -p storage/app/certificates/elfatoora
cp votre-certificat.pem storage/app/certificates/elfatoora/
cp votre-cle-privee.pem storage/app/certificates/elfatoora/
chmod 600 storage/app/certificates/elfatoora/*
```

3. **Configurer dans l'interface**:
   - Aller dans Paramètres > El Fatoora
   - Activer El Fatoora
   - Saisir la clé API
   - Télécharger le certificat et la clé privée

### Configuration IA

1. **Anthropic Claude** (Assistant IA):
   - Créer un compte sur https://console.anthropic.com
   - Générer une clé API
   - Ajouter dans `.env`: `ANTHROPIC_API_KEY=sk-ant-...`

2. **Google Cloud Vision** (OCR):
   - Créer un projet sur Google Cloud Console
   - Activer l'API Vision
   - Télécharger le fichier JSON de credentials
   - Placer dans `storage/app/credentials/google-vision.json`

---

## Sécurité

### 1. SSL/TLS (Let's Encrypt)

```bash
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d votre-domaine.tn
sudo systemctl reload nginx

# Renouvellement automatique
sudo certbot renew --dry-run
```

### 2. Firewall

```bash
sudo ufw allow 22/tcp
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw enable
```

### 3. Sécurité PostgreSQL

```bash
# Éditer pg_hba.conf
sudo nano /etc/postgresql/15/main/pg_hba.conf

# Autoriser uniquement localhost
# local   all             all                                     md5
# host    all             all             127.0.0.1/32            md5

sudo systemctl restart postgresql
```

### 4. Sécurité des Fichiers

```bash
# Permissions strictes
find /var/www/comptapro -type f -exec chmod 644 {} \;
find /var/www/comptapro -type d -exec chmod 755 {} \;
chmod 600 /var/www/comptapro/.env
```

---

## Optimisation

### 1. OPcache (PHP)

```bash
sudo nano /etc/php/8.2/fpm/conf.d/10-opcache.ini
```

```ini
opcache.enable=1
opcache.memory_consumption=256
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=20000
opcache.validate_timestamps=0
opcache.save_comments=1
opcache.fast_shutdown=1
```

### 2. PostgreSQL

```bash
sudo nano /etc/postgresql/15/main/postgresql.conf
```

```
shared_buffers = 1GB
effective_cache_size = 3GB
maintenance_work_mem = 256MB
checkpoint_completion_target = 0.9
wal_buffers = 16MB
default_statistics_target = 100
random_page_cost = 1.1
effective_io_concurrency = 200
work_mem = 5MB
min_wal_size = 1GB
max_wal_size = 4GB
max_worker_processes = 4
max_parallel_workers_per_gather = 2
max_parallel_workers = 4
```

### 3. Redis

```bash
sudo nano /etc/redis/redis.conf
```

```
maxmemory 512mb
maxmemory-policy allkeys-lru
```

---

## Sauvegarde

### Script de Sauvegarde Automatique

```bash
sudo nano /usr/local/bin/backup-comptapro.sh
```

```bash
#!/bin/bash

BACKUP_DIR="/backups/comptapro"
DATE=$(date +%Y-%m-%d_%H-%M-%S)
APP_DIR="/var/www/comptapro"

# Créer le répertoire de sauvegarde
mkdir -p $BACKUP_DIR

# Sauvegarde de la base de données
PGPASSWORD="VotreMotDePasse" pg_dump -h localhost -U comptapro comptapro | gzip > "$BACKUP_DIR/db_$DATE.sql.gz"

# Sauvegarde des fichiers
tar -czf "$BACKUP_DIR/files_$DATE.tar.gz" \
    $APP_DIR/storage/app \
    $APP_DIR/.env

# Garder seulement les 30 dernières sauvegardes
find $BACKUP_DIR -name "db_*.sql.gz" -mtime +30 -delete
find $BACKUP_DIR -name "files_*.tar.gz" -mtime +30 -delete

echo "Sauvegarde terminée: $DATE"
```

```bash
sudo chmod +x /usr/local/bin/backup-comptapro.sh

# Ajouter au cron (tous les jours à 2h du matin)
sudo crontab -e
0 2 * * * /usr/local/bin/backup-comptapro.sh >> /var/log/comptapro-backup.log 2>&1
```

---

## Maintenance

### Mise à Jour de l'Application

```bash
cd /var/www/comptapro

# Mode maintenance
php artisan down

# Mise à jour du code
git pull origin main

# Mise à jour des dépendances
composer install --optimize-autoloader --no-dev
npm install && npm run build

# Migrations
php artisan migrate --force

# Optimisations
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

# Redémarrer les workers
sudo supervisorctl restart comptapro-worker:*

# Sortir du mode maintenance
php artisan up
```

### Nettoyage

```bash
# Nettoyer les vieux logs
php artisan log:clear --keep=30

# Nettoyer le cache
php artisan cache:clear
php artisan view:clear
php artisan route:clear
php artisan config:clear

# Optimiser la base de données
php artisan db:vacuum
```

---

## Dépannage

### Problème: Erreur 500

```bash
# Vérifier les logs
tail -f storage/logs/laravel.log
tail -f /var/log/nginx/error.log

# Vérifier les permissions
ls -la storage/
ls -la bootstrap/cache/
```

### Problème: Queue ne fonctionne pas

```bash
# Vérifier le statut des workers
sudo supervisorctl status

# Redémarrer les workers
sudo supervisorctl restart comptapro-worker:*

# Vérifier Redis
redis-cli ping
```

### Problème: El Fatoora ne se connecte pas

```bash
# Tester la connexion
php artisan tinker
>>> app(\App\Services\ElFatoora\ElFatooraService::class)->testConnection();

# Vérifier les certificats
ls -la storage/app/certificates/elfatoora/
```

### Problème: Performance lente

```bash
# Vérifier l'utilisation des ressources
htop

# Vérifier les requêtes PostgreSQL lentes
sudo -u postgres psql -d comptapro
SELECT * FROM pg_stat_activity WHERE state = 'active';

# Optimiser les tables
php artisan db:optimize
```

---

## Support

Pour toute assistance:
- **Documentation**: https://docs.comptapro.tn
- **Support**: support@comptapro.tn
- **GitHub Issues**: https://github.com/votre-repo/comptapro-tunisia/issues

---

**Version**: 1.0.0
**Dernière mise à jour**: Janvier 2025
