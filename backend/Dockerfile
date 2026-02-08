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

# 5. Set working directory and copy app files
WORKDIR /var/www/html
COPY . /var/www/html/

# 6. Configure Apache
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# 7. COPY the separate config file into Apache's config folder
# We overwrite the default 000-default.conf
COPY railway.conf /etc/apache2/sites-available/000-default.conf

# 8. Set permissions
RUN chown -R www-data:www-data /var/www/html

# 9. Startup Script (Updated to handle MPM and Port)
RUN echo '#!/bin/sh\n\
a2dismod mpm_event mpm_worker || true\n\
a2enmod mpm_prefork || true\n\
sed -i "s/Listen 80/Listen ${PORT}/g" /etc/apache2/ports.conf\n\
exec apache2-foreground' > /usr/local/bin/start.sh && chmod +x /usr/local/bin/start.sh

# Railway environment setup
ENV PORT=80
CMD ["/usr/local/bin/start.sh"]