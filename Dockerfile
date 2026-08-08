FROM php:8.2-cli-alpine

# System dependencies
RUN apk add --no-cache \
    curl \
    unzip \
    git \
    libpng-dev \
    libzip-dev \
    oniguruma-dev \
    && docker-php-ext-install \
    pdo_mysql \
    mbstring \
    zip \
    gd \
    bcmath

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy application files
# BUILD_TIMESTAMP: 2026-07-30-01
COPY . .

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Set proper permissions and ownership for non-root user
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Copy start script
COPY docker/start.sh /start.sh
RUN chmod +x /start.sh

# Switch to non-root user for security
USER www-data

EXPOSE 8000

CMD ["/start.sh"]