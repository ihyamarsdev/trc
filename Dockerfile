# Gunakan image FrankenPHP resmi berbasis Caddy
FROM dunglas/frankenphp:1-php8.4.8-alpine

# Pindah ke direktori kerja
WORKDIR /app

RUN install-php-extensions \
    pdo_pgsql \
	gd \
	intl \
	zip \
	opcache \
    bcmath \
    pcntl

# Salin file Laravel
COPY . .

# Install dependency Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Install dependency Laravel
RUN composer install --no-dev --no-scripts --prefer-dist --optimize-autoloader

RUN php artisan storage:link

EXPOSE 8000

ENTRYPOINT [ "php", "artisan", "octane:frankenphp", "--workers=2", "--max-requests=30", "--port=8000"]
