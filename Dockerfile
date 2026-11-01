FROM php:8.2-fpm

# Install dependensi sistem & ekstensi PHP
RUN apt-get update && apt-get install -y \
    git unzip libpng-dev libonig-dev libxml2-dev zip curl \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Copy Composer dari official image
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY . .

RUN composer install --no-dev --optimize-autoloader

# Atur port dan jalankan server
EXPOSE 8000
CMD php artisan serve --host=0.0.0.0 --port=8000