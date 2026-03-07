# =============================================================================
# Multi-Stage Build untuk Optimasi Layer Caching
# =============================================================================

# -----------------------------------------------------------------------------
# Stage 1: Base dengan PHP Extensions
# Stage ini menyiapkan base image dengan semua extensions yang diperlukan
# -----------------------------------------------------------------------------
FROM dunglas/frankenphp:1-php8.4.8-alpine AS base

# Install PHP extensions yang dibutuhkan sebelum composer install
RUN install-php-extensions \
    pdo_pgsql \
    gd \
    intl \
    zip \
    opcache \
    bcmath \
    pcntl \
    && rm -rf /var/cache/apk/*

# Copy composer binary dari official composer image
COPY --from=composer:2.8 /usr/bin/composer /usr/bin/composer

# -----------------------------------------------------------------------------
# Stage 2: Composer Dependencies
# Install dependencies di environment yang sudah punya semua extensions
# -----------------------------------------------------------------------------
FROM base AS composer

# Set working directory
WORKDIR /app

# Copy hanya composer files untuk layer caching yang optimal
COPY composer.json composer.lock ./

# Install production dependencies
# Extensions sudah tersedia dari base stage
RUN composer install \
    --no-dev \
    --no-scripts \
    --no-autoloader \
    --prefer-dist \
    --optimize-autoloader \
    --no-interaction \
    --no-progress

# -----------------------------------------------------------------------------
# Stage 3: Application Image
# Final image yang berisi aplikasi lengkap
# -----------------------------------------------------------------------------
FROM base AS production

# Build arguments untuk flexibilitas
ARG APP_ENV=production
ARG APP_DEBUG=false

# Environment variables
ENV CADDY_GLOBAL_OPTIONS="auto_https off" \
    SERVER_NAME=":80" \
    APP_ENV=${APP_ENV} \
    APP_DEBUG=${APP_DEBUG} \
    PHP_OPCACHE_ENABLE=1 \
    PHP_OPCACHE_MEMORY_CONSUMPTION=256 \
    PHP_OPCACHE_MAX_ACCELERATED_FILES=20000

# Working directory
WORKDIR /app

# Copy composer dependencies dari composer stage
COPY --from=composer /app/vendor ./vendor

# Copy composer lock untuk version tracking
COPY --from=composer /app/composer.lock ./

# Copy application source code
# File-file di-copy terpisah untuk optimal layer caching
COPY artisan ./
COPY bootstrap ./bootstrap
COPY config ./config
COPY database ./database
COPY public ./public
COPY resources ./resources
COPY routes ./routes
COPY storage ./storage
COPY lang ./lang

# Copy composer files
COPY composer.json composer.lock ./

# Copy composer binary untuk dump-autoload
COPY --from=composer:2.8 /usr/bin/composer /usr/bin/composer

# Generate optimized autoloader
RUN composer dump-autoload \
    --optimize \
    --classmap-authoritative \
    --no-dev \
    --quiet

# Set proper permissions untuk storage dan cache
RUN chmod -R 775 storage bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

# Add healthcheck untuk container orchestration
HEALTHCHECK --interval=30s --timeout=5s --start-period=10s --retries=3 \
    CMD wget --quiet --tries=1 --spider http://localhost:8000/ || exit 1

# Configure Octane with FrankenPHP
ENTRYPOINT ["php", "artisan", "octane:frankenphp", \
    "--workers=5", \
    "--max-requests=500", \
    "--log-level=warn", \
    "--port=8000"]
