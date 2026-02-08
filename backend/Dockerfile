# syntax=docker/dockerfile:1

# Use PHP 8.2 with Apache - Railway compatible
FROM php:8.2-apache

# Install system dependencies and PHP extensions
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

# Configure Apache MPM modules properly
RUN a2dismod mpm_event mpm_worker 2>/dev/null || true
RUN a2enmod mpm_prefork
RUN a2enmod rewrite

# Create MPM prefork configuration to avoid conflicts
RUN echo '<IfModule mpm_prefork_module>' > /etc/apache2/conf-available/mpm_prefork.conf \
    && echo '    StartServers 2' >> /etc/apache2/conf-available/mpm_prefork.conf \
    && echo '    MinSpareServers 2' >> /etc/apache2/conf-available/mpm_prefork.conf \
    && echo '    MaxSpareServers 5' >> /etc/apache2/conf-available/mpm_prefork.conf \
    && echo '    MaxRequestWorkers 10' >> /etc/apache2/conf-available/mpm_prefork.conf \
    && echo '    MaxConnectionsPerChild 1000' >> /etc/apache2/conf-available/mpm_prefork.conf \
    && echo '</IfModule>' >> /etc/apache2/conf-available/mpm_prefork.conf

# Enable the MPM configuration
RUN a2enconf mpm_prefork

# Use production PHP configuration
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Configure Apache for Railway (handle dynamic ports)
RUN echo 'Listen ${PORT:-80}' > /etc/apache2/ports.conf
RUN sed -i 's/80/${PORT:-80}/g' /etc/apache2/sites-available/000-default.conf

# Set proper permissions
RUN chown -R www-data:www-data /var/www/html

# Copy application files
COPY --chown=www-data:www-data ./ /var/www/html/

# Ensure Apache can write to necessary directories
RUN chmod -R 755 /var/www/html

# Create startup script to handle Railway's dynamic port
RUN echo '#!/bin/bash' > /usr/local/bin/start-apache \
    && echo 'export APACHE_RUN_USER=www-data' >> /usr/local/bin/start-apache \
    && echo 'export APACHE_RUN_GROUP=www-data' >> /usr/local/bin/start-apache \
    && echo 'export APACHE_PID_FILE=/var/run/apache2/apache2.pid' >> /usr/local/bin/start-apache \
    && echo 'export APACHE_RUN_DIR=/var/run/apache2' >> /usr/local/bin/start-apache \
    && echo 'export APACHE_LOCK_DIR=/var/lock/apache2' >> /usr/local/bin/start-apache \
    && echo 'export APACHE_LOG_DIR=/var/log/apache2' >> /usr/local/bin/start-apache \
    && echo 'mkdir -p $APACHE_RUN_DIR $APACHE_LOCK_DIR $APACHE_LOG_DIR' >> /usr/local/bin/start-apache \
    && echo 'exec apache2 -DFOREGROUND' >> /usr/local/bin/start-apache \
    && chmod +x /usr/local/bin/start-apache

# Switch to non-privileged user
USER www-data

# Expose port (Railway will override this)
EXPOSE 80

# Start Apache
CMD ["/usr/local/bin/start-apache"]
