# Imagen base con PHP y extensiones necesarias
FROM php:8.3-fpm

# Instalar dependencias del sistema
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    zip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_mysql zip

# Instalar Composer
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

# Establecer el directorio de trabajo
WORKDIR /var/www

# Copiar composer.json y composer.lock
COPY composer.json composer.lock ./

# Instalar dependencias de PHP
RUN composer install --no-scripts --no-autoloader

# Copiar todo el código
COPY . .

# Generar el autoload
RUN composer dump-autoload --optimize

# Exponer el puerto que usará Laravel
EXPOSE 8000

# Comando para iniciar Laravel
CMD php artisan serve --host=0.0.0.0 --port=8000

