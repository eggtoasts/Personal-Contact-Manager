# syntax=docker/dockerfile:1

FROM php:8.2-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    libzip-dev \
    unzip \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd zip pdo pdo_mysql

# Disable ALL MPM modules first to avoid conflicts
RUN a2dismod mpm_event || true
RUN a2dismod mpm_worker || true
RUN a2dismod mpm_prefork || true

# Remove any existing MPM module symlinks
RUN rm -f /etc/apache2/mods-enabled/mpm_*

# Enable only prefork MPM
RUN a2enmod mpm_prefork

# Enable rewrite module
RUN a2enmod rewrite

# Use production PHP configuration
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Configure Apache for Railway dynamic ports
RUN sed -i 's/Listen 80/Listen ${PORT:-80}/' /etc/apache2/ports.conf
RUN sed -i 's/:80/:${PORT:-80}/' /etc/apache2/sites-available/000-default.conf

# Copy application files
COPY --chown=www-data:www-data ./ /var/www/html/

# Set proper permissions
RUN chown -R www-data:www-data /var/www/html
RUN chmod -R 755 /var/www/html

# Switch to non-privileged user
USER www-data

# Expose port
EXPOSE 80

# Start Apache in foreground
CMD ["apache2-foreground"]
