# 1. Use the Apache + PHP base image
FROM php:8.2-apache

# 2. Install dependencies
RUN apt-get update && apt-get install -y \
    libzip-dev unzip libpng-dev libjpeg-dev libfreetype6-dev \
    && rm -rf /var/lib/apt/lists/*

# 3. Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd zip pdo pdo_mysql mysqli

# 4. Enable Apache Modules
RUN a2enmod rewrite headers

# 5. Set working directory and copy files
WORKDIR /var/www/html
COPY . /var/www/html/

# 6. Configure Apache for Railway
# This setup replaces the complex "cat" command with a simpler echo approach
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf
# Ensure headers module is enabled
RUN a2enmod rewrite headers

# Create the Site Config with robust CORS support
RUN echo '<VirtualHost *:${PORT}>\n\
    DocumentRoot /var/www/html\n\
    \n\
    # Global CORS settings\n\
    Header always set Access-Control-Allow-Origin "https://sonic-contact-manager.up.railway.app"\n\
    Header always set Access-Control-Allow-Methods "POST, GET, OPTIONS, DELETE, PUT"\n\
    Header always set Access-Control-Allow-Headers "Content-Type, Authorization, X-Requested-With"\n\
\n\
    <Directory "/var/www/html">\n\
        AllowOverride All\n\
        Require all granted\n\
\n\
        RewriteEngine On\n\
\n\
        # 1. Handle Preflight (OPTIONS) requests at the Apache level\n\
        RewriteCond %{REQUEST_METHOD} OPTIONS\n\
        RewriteRule ^(.*)$ $1 [R=200,L]\n\
\n\
        # 2. Map clean URLs to PHP files\n\
        RewriteCond %{REQUEST_FILENAME} !-f\n\
        RewriteRule ^Login$ Login.php [L]\n\
        RewriteRule ^Register$ Register.php [L]\n\
        RewriteRule ^getContacts$ getContacts.php [L]\n\
        RewriteRule ^addContact$ addContact.php [L]\n\
        RewriteRule ^updateContact$ updateContact.php [L]\n\
        RewriteRule ^deleteContact$ deleteContact.php [L]\n\
        RewriteRule ^searchContacts$ searchContacts.php [L]\n\
    </Directory>\n\
</VirtualHost>' > /etc/apache2/sites-available/000-default.conf
# 8. Set permissions
RUN chown -R www-data:www-data /var/www/html

# 9. Startup Script
RUN echo '#!/bin/sh\n\
# Force disable conflicting MPMs and enable prefork\n\
a2dismod mpm_event mpm_worker || true\n\
a2enmod mpm_prefork || true\n\
\n\
# Update port for Railway\n\
sed -i "s/Listen 80/Listen ${PORT}/g" /etc/apache2/ports.conf\n\
\n\
# Start Apache\n\
exec apache2-foreground' > /usr/local/bin/start.sh && chmod +x /usr/local/bin/start.sh

# Railway default port
ENV PORT=80

CMD ["/usr/local/bin/start.sh"]