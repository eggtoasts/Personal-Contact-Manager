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

# Use production PHP configuration
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Enable Apache modules
RUN a2enmod rewrite headers

# Configure Apache for Railway (dynamic port support)
RUN sed -i 's/Listen 80/Listen ${PORT:-80}/' /etc/apache2/ports.conf
RUN sed -i 's/:80/:${PORT:-80}/' /etc/apache2/sites-available/000-default.conf

# Configure Apache logging for Railway (stdout/stderr)
RUN sed -i 's|ErrorLog .*|ErrorLog /proc/self/fd/2|' /etc/apache2/apache2.conf \
    && sed -i 's|CustomLog .*|CustomLog /proc/self/fd/1 combined|' /etc/apache2/sites-available/000-default.conf

# Create Apache configuration with CORS and clean URLs
RUN cat > /etc/apache2/conf-available/api.conf << 'EOF'
# CORS Headers for API
Header always set Access-Control-Allow-Origin "*"
Header always set Access-Control-Allow-Methods "GET, POST, PUT, DELETE, OPTIONS"
Header always set Access-Control-Allow-Headers "Content-Type, Authorization, X-Requested-With"

# Handle preflight OPTIONS requests
RewriteEngine On
RewriteCond %{REQUEST_METHOD} OPTIONS
RewriteRule ^(.*)$ $1 [R=200,L]

# Clean URL rewrites for API endpoints
RewriteRule ^api/?$ api_info.php [L]
RewriteRule ^info/?$ api_info.php [L]
RewriteRule ^swagger/?$ swagger.php [L]
RewriteRule ^docs/?$ swagger.php [L]
RewriteRule ^swagger\.json$ swagger_json.php [L]
RewriteRule ^logs/?$ logs.php [L]
RewriteRule ^db/?$ db_test.php [L]
RewriteRule ^test-logs/?$ log_test.php [L]

# API endpoint rewrites
RewriteRule ^Login/?$ Login.php [L]
RewriteRule ^Register/?$ Register.php [L]
RewriteRule ^getContacts/?$ getContacts.php [L]
RewriteRule ^addContact/?$ addContact.php [L]
RewriteRule ^updateContact/?$ updateContact.php [L]
RewriteRule ^deleteContact/?$ deleteContact.php [L]
RewriteRule ^editContact/?$ editContact.php [L]
RewriteRule ^searchContacts/?$ searchContacts.php [L]

# Health check endpoint
RewriteRule ^health/?$ - [E=no_log:1]
RewriteCond %{REQUEST_URI} ^/health/?$
RewriteRule . - [R=200,L,E=response:healthy]
EOF

# Enable the API configuration
RUN a2enconf api

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . /var/www/html/

# Set proper permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && touch /var/www/html/requests.log \
    && chown www-data:www-data /var/www/html/requests.log \
    && chmod 666 /var/www/html/requests.log

# Configure PHP error logging for Railway
RUN echo "log_errors = On" >> /usr/local/etc/php/php.ini \
    && echo "error_log = /proc/self/fd/2" >> /usr/local/etc/php/php.ini \
    && echo "display_errors = Off" >> /usr/local/etc/php/php.ini

# Create startup script for Railway
RUN cat > /usr/local/bin/start-apache << 'EOF'
#!/bin/bash

echo "Starting Apache for Railway..."

# Update port configuration if PORT is set
if [ ! -z "$PORT" ]; then
    echo "Railway PORT detected: $PORT"
    sed -i "s/Listen \${PORT:-80}/Listen $PORT/" /etc/apache2/ports.conf
    sed -i "s/:\${PORT:-80}/:$PORT/" /etc/apache2/sites-available/000-default.conf
fi

# Set proper environment for Apache
export APACHE_RUN_USER=www-data
export APACHE_RUN_GROUP=www-data
export APACHE_PID_FILE=/var/run/apache2/apache2.pid
export APACHE_RUN_DIR=/var/run/apache2
export APACHE_LOCK_DIR=/var/lock/apache2
export APACHE_LOG_DIR=/var/log/apache2

# Create necessary directories
mkdir -p $APACHE_RUN_DIR $APACHE_LOCK_DIR $APACHE_LOG_DIR

# Start Apache in foreground
echo "Apache starting on port ${PORT:-80}"
exec apache2 -DFOREGROUND
EOF

RUN chmod +x /usr/local/bin/start-apache

# Expose port (Railway will override this)
EXPOSE 80

# Start Apache
CMD ["/usr/local/bin/start-apache"]
