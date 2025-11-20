FROM php:8.2-fpm-alpine

# Arguments de build
ARG USER_ID=1000
ARG GROUP_ID=1000

# Installation des dépendances système
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    zip \
    unzip \
    postgresql-dev \
    oniguruma-dev \
    libxml2-dev \
    icu-dev \
    autoconf \
    g++ \
    make \
    supervisor \
    nginx

# Installation des extensions PHP
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo \
        pdo_pgsql \
        pgsql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
        intl \
        opcache \
        soap \
        sockets

# Installation de Redis extension
RUN pecl install redis \
    && docker-php-ext-enable redis

# Installation de Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configuration PHP pour production
RUN cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Configuration PHP personnalisée
COPY docker/php/conf.d/opcache.ini $PHP_INI_DIR/conf.d/
COPY docker/php/conf.d/uploads.ini $PHP_INI_DIR/conf.d/

# Créer un utilisateur non-root
RUN addgroup -g ${GROUP_ID} -S www && \
    adduser -u ${USER_ID} -S www -G www

# Définir le répertoire de travail
WORKDIR /var/www

# Copier les fichiers de l'application
COPY --chown=www:www . /var/www

# Installation des dépendances PHP
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Permissions
RUN chown -R www:www /var/www \
    && chmod -R 755 /var/www/storage \
    && chmod -R 755 /var/www/bootstrap/cache

# Exposer le port PHP-FPM
EXPOSE 9000

# Utiliser l'utilisateur non-root
USER www

# Commande de démarrage
CMD ["php-fpm"]
