FROM php:8.3-apache

# Installer les extensions PHP nécessaires
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libpq-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    nodejs \
    npm \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_pgsql pdo_mysql gd \
    && a2enmod rewrite

# Installer Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configurer Apache
RUN echo '<Directory /var/www/html/public>\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>' > /etc/apache2/conf-available/laravel.conf \
    && a2enconf laravel \
    && echo 'ServerName localhost' >> /etc/apache2/apache2.conf

# Copier le code
WORKDIR /var/www/html
COPY . .

# Installer les dépendances
RUN composer install --no-dev --optimize-autoloader \
    && npm install \
    && npm run build \
    && chown -R www-data:www-data storage bootstrap/cache

# Exposer le port
EXPOSE 80

# Démarrer Apache
# Script de démarrage avec migrations
RUN echo '#!/bin/bash\n\
php artisan migrate --force\n\
apache2-foreground' > /start.sh \
    && chmod +x /start.sh

CMD ["/start.sh"]