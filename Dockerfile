FROM php:8.3-fpm

# Instalar extensiones necesarias
RUN apt-get update && apt-get install -y \
    git unzip libpng-dev libjpeg-dev libfreetype6-dev libzip-dev zip libgmp-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_mysql zip gmp

# Composer
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer
WORKDIR /var/www
ENV COMPOSER_ALLOW_SUPERUSER=1

# --------------------
# Copiar todo el proyecto antes de instalar dependencias
# --------------------
COPY . .

# Instalar dependencias
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Ajustar permisos
RUN chown -R www-data:www-data storage bootstrap/cache

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
EXPOSE 8000

