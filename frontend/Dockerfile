# 1. Use PHP + Apache base image
FROM php:8.2-apache

# 2. Enable Apache rewrite module (important for routing)
RUN a2enmod rewrite

# 3. Install PHP extensions for database connectivity
RUN docker-php-ext-install mysqli pdo pdo_mysql

# 4. Copy your repo files into the Apache web root
COPY . /var/www/html/

# 5. Set ownership so Apache (www-data) can access the files
RUN chown -R www-data:www-data /var/www/html

# ... (rest of your Dockerfile above) ...

# 6. Create a startup script that fixes the MPM error and sets the Port
RUN echo '#!/bin/sh \n\
# Fix "More than one MPM loaded" error \n\
# We ensure only mpm_prefork is enabled \n\
a2dismod mpm_event mpm_worker || true \n\
a2enmod mpm_prefork || true \n\
\n\
# Update ports for Railway \n\
sed -i "s/Listen 80/Listen ${PORT}/g" /etc/apache2/ports.conf \n\
sed -i "s/<VirtualHost \*:80>/<VirtualHost *:${PORT}>/g" /etc/apache2/sites-available/000-default.conf \n\
\n\
exec apache2-foreground' > /usr/local/bin/railway-entrypoint.sh

RUN chmod +x /usr/local/bin/railway-entrypoint.sh

ENTRYPOINT ["/usr/local/bin/railway-entrypoint.sh"]