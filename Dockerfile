# Use official PHP image
FROM php:8.2-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git curl zip unzip libpng-dev libonig-dev libxml2-dev \
    libzip-dev libcurl4-openssl-dev pkg-config libssl-dev \
    libjpeg-dev libfreetype6-dev

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql mbstring zip exif pcntl gd

# Install Redis extension (optional but recommended)
RUN pecl install redis && docker-php-ext-enable redis

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Create non-root user
RUN useradd -G www-data,root -u 1000 -d /home/roman roman \
    && mkdir -p /home/roman/.composer \
    && chown -R roman:roman /home/roman

# Set working directory
WORKDIR /var/www

# Copy application files
COPY . .

# Install Laravel dependencies (optimized)
RUN composer install --no-dev --optimize-autoloader

# Set permissions
RUN chown -R roman:www-data /var/www \
    && chmod -R 775 /var/www/storage \
    && chmod -R 775 /var/www/bootstrap/cache

# Switch to non-root user
USER roman

# Expose port
EXPOSE 9000

# Start PHP-FPM
CMD ["php-fpm"]