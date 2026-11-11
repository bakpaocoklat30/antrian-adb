FROM php:8.4-fpm

# Install dependensi sistem dan default-mysql-client (DIJADIKAN SATU DI ATAS)
RUN apt-get update && apt-get install -y \
    git curl libpng-dev libonig-dev libxml2-dev zip unzip libzip-dev \
    libjpeg-dev libfreetype6-dev default-mysql-client

# Install extension PHP
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Ambil Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Atur izin agar Laravel bisa menulis log/cache
RUN chown -R www-data:www-data /var/www

# Ganti ke user biasa (Posisinya WAJIB paling bawah)
USER www-data