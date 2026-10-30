FROM php:8.3-apache

ENV APP_ENV=production APP_DEBUG=false

# Extensions PHP + libwebp-dev pour le support WebP
RUN apt-get update && apt-get install -y \
    git unzip libpq-dev libpng-dev libjpeg-dev libfreetype6-dev libicu-dev libzip-dev libwebp-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install pdo pdo_pgsql pdo_mysql gd intl zip \
    && a2enmod rewrite

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Apache → pointer vers /public
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Code
WORKDIR /var/www/html
COPY docker/php-uploads.ini /usr/local/etc/php/conf.d/99-uploads.ini
COPY . .

# Composer uniquement (build Vite déjà fait en local)
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist \
    && php artisan config:clear \
    && php artisan route:clear \
    && php artisan view:clear \
    && chown -R www-data:www-data storage bootstrap/cache public/build

EXPOSE 80

COPY docker/entrypoint.sh /usr/local/bin/render-entrypoint
RUN chmod +x /usr/local/bin/render-entrypoint

ENTRYPOINT ["render-entrypoint"]
CMD ["apache2-foreground"]