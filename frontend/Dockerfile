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

# 6. Create a startup script to handle Railway's dynamic port
# This script finds the default port 80 and replaces it with the $PORT variable
RUN echo '#!/bin/sh \n\
sed -i "s/Listen 80/Listen ${PORT}/g" /etc/apache2/ports.conf \n\
sed -i "s/<VirtualHost \*:80>/<VirtualHost *:${PORT}>/g" /etc/apache2/sites-available/000-default.conf \n\
exec apache2-foreground' > /usr/local/bin/railway-entrypoint.sh

# 7. Make the script executable
RUN chmod +x /usr/local/bin/railway-entrypoint.sh

# 8. Tell Docker to run our script on startup
ENTRYPOINT ["/usr/local/bin/railway-entrypoint.sh"]