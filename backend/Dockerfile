# syntax=docker/dockerfile:1

FROM php:8.2-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    libzip-dev \
    unzip \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install zip pdo pdo_mysql

# Disable all MPM modules first, then enable only prefork
RUN a2dismod mpm_event mpm_worker || true
RUN a2enmod mpm_prefork

# Enable Apache rewrite module (commonly needed for PHP apps)
RUN a2enmod rewrite

# Use the default production configuration for PHP runtime arguments
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Set correct permissions for Apache
RUN chown -R www-data:www-data /var/www/html

# Copy app files from the app directory
COPY --chown=www-data:www-data ./ /var/www/html/

# Create a custom Apache configuration to ensure MPM settings
RUN echo 'LoadModule mpm_prefork_module modules/mod_mpm_prefork.so' > /etc/apache2/mods-available/mpm_prefork.load

# Switch to a non-privileged user
USER www-data

# Expose port 80
EXPOSE 80

# Start Apache in foreground
CMD ["apache2-foreground"]