# Multi-stage build for PHP-FPM + Nginx
FROM php:8.2-fpm as php

# Install system dependencies
RUN apt-get update && apt-get install -y \
    libzip-dev \
    unzip \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    nginx \
    supervisor \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd zip pdo pdo_mysql

# Use production PHP configuration
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Create application directory
WORKDIR /var/www/html

# Copy application files
COPY . /var/www/html/

# Set proper permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Create nginx configuration
RUN echo 'server {' > /etc/nginx/sites-available/default \
    && echo '    listen 80;' >> /etc/nginx/sites-available/default \
    && echo '    root /var/www/html;' >> /etc/nginx/sites-available/default \
    && echo '    index index.php;' >> /etc/nginx/sites-available/default \
    && echo '' >> /etc/nginx/sites-available/default \
    && echo '    # CORS headers' >> /etc/nginx/sites-available/default \
    && echo '    add_header Access-Control-Allow-Origin "*" always;' >> /etc/nginx/sites-available/default \
    && echo '    add_header Access-Control-Allow-Methods "GET, POST, PUT, DELETE, OPTIONS" always;' >> /etc/nginx/sites-available/default \
    && echo '    add_header Access-Control-Allow-Headers "Content-Type, Authorization, X-Requested-With" always;' >> /etc/nginx/sites-available/default \
    && echo '' >> /etc/nginx/sites-available/default \
    && echo '    # Handle OPTIONS requests' >> /etc/nginx/sites-available/default \
    && echo '    if ($request_method = OPTIONS) {' >> /etc/nginx/sites-available/default \
    && echo '        return 200;' >> /etc/nginx/sites-available/default \
    && echo '    }' >> /etc/nginx/sites-available/default \
    && echo '' >> /etc/nginx/sites-available/default \
    && echo '    # API routing' >> /etc/nginx/sites-available/default \
    && echo '    location / {' >> /etc/nginx/sites-available/default \
    && echo '        try_files $uri $uri/ @api;' >> /etc/nginx/sites-available/default \
    && echo '    }' >> /etc/nginx/sites-available/default \
    && echo '' >> /etc/nginx/sites-available/default \
    && echo '    location @api {' >> /etc/nginx/sites-available/default \
    && echo '        rewrite ^/(.*)$ /router.php last;' >> /etc/nginx/sites-available/default \
    && echo '    }' >> /etc/nginx/sites-available/default \
    && echo '' >> /etc/nginx/sites-available/default \
    && echo '    location ~ \.php$ {' >> /etc/nginx/sites-available/default \
    && echo '        fastcgi_pass 127.0.0.1:9000;' >> /etc/nginx/sites-available/default \
    && echo '        fastcgi_index index.php;' >> /etc/nginx/sites-available/default \
    && echo '        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;' >> /etc/nginx/sites-available/default \
    && echo '        include fastcgi_params;' >> /etc/nginx/sites-available/default \
    && echo '    }' >> /etc/nginx/sites-available/default \
    && echo '}' >> /etc/nginx/sites-available/default

# Create supervisor configuration
RUN echo '[supervisord]' > /etc/supervisor/conf.d/supervisord.conf \
    && echo 'nodaemon=true' >> /etc/supervisor/conf.d/supervisord.conf \
    && echo '' >> /etc/supervisor/conf.d/supervisord.conf \
    && echo '[program:nginx]' >> /etc/supervisor/conf.d/supervisord.conf \
    && echo 'command=nginx -g "daemon off;"' >> /etc/supervisor/conf.d/supervisord.conf \
    && echo 'autostart=true' >> /etc/supervisor/conf.d/supervisord.conf \
    && echo 'autorestart=true' >> /etc/supervisor/conf.d/supervisord.conf \
    && echo 'priority=10' >> /etc/supervisor/conf.d/supervisord.conf \
    && echo 'stdout_logfile=/var/log/nginx.log' >> /etc/supervisor/conf.d/supervisord.conf \
    && echo 'stderr_logfile=/var/log/nginx.log' >> /etc/supervisor/conf.d/supervisord.conf \
    && echo '' >> /etc/supervisor/conf.d/supervisord.conf \
    && echo '[program:php-fpm]' >> /etc/supervisor/conf.d/supervisord.conf \
    && echo 'command=php-fpm' >> /etc/supervisor/conf.d/supervisord.conf \
    && echo 'autostart=true' >> /etc/supervisor/conf.d/supervisord.conf \
    && echo 'autorestart=true' >> /etc/supervisor/conf.d/supervisord.conf \
    && echo 'priority=5' >> /etc/supervisor/conf.d/supervisord.conf \
    && echo 'stdout_logfile=/var/log/php-fpm.log' >> /etc/supervisor/conf.d/supervisord.conf \
    && echo 'stderr_logfile=/var/log/php-fpm.log' >> /etc/supervisor/conf.d/supervisord.conf

# Copy and setup startup script
COPY start.sh /start.sh
RUN chmod +x /start.sh

# Create simple router.php
RUN echo '<?php' > /var/www/html/router.php \
    && echo 'header("Content-Type: application/json");' >> /var/www/html/router.php \
    && echo 'header("Access-Control-Allow-Origin: *");' >> /var/www/html/router.php \
    && echo 'header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");' >> /var/www/html/router.php \
    && echo 'header("Access-Control-Allow-Headers: Content-Type, Authorization");' >> /var/www/html/router.php \
    && echo '' >> /var/www/html/router.php \
    && echo 'if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {' >> /var/www/html/router.php \
    && echo '    http_response_code(200);' >> /var/www/html/router.php \
    && echo '    exit();' >> /var/www/html/router.php \
    && echo '}' >> /var/www/html/router.php \
    && echo '' >> /var/www/html/router.php \
    && echo '$path = trim($_SERVER["REQUEST_URI"], "/");' >> /var/www/html/router.php \
    && echo '$routes = [' >> /var/www/html/router.php \
    && echo '    "Login" => "Login.php",' >> /var/www/html/router.php \
    && echo '    "Register" => "Register.php",' >> /var/www/html/router.php \
    && echo '    "addContact" => "addContact.php",' >> /var/www/html/router.php \
    && echo '    "getContacts" => "getContacts.php",' >> /var/www/html/router.php \
    && echo '    "updateContact" => "updateContact.php",' >> /var/www/html/router.php \
    && echo '    "deleteContact" => "deleteContact.php",' >> /var/www/html/router.php \
    && echo '    "editContact" => "editContact.php",' >> /var/www/html/router.php \
    && echo '    "searchContacts" => "searchContacts.php"' >> /var/www/html/router.php \
    && echo '];' >> /var/www/html/router.php \
    && echo '' >> /var/www/html/router.php \
    && echo 'if (isset($routes[$path]) && file_exists($routes[$path])) {' >> /var/www/html/router.php \
    && echo '    require $routes[$path];' >> /var/www/html/router.php \
    && echo '} else {' >> /var/www/html/router.php \
    && echo '    http_response_code(404);' >> /var/www/html/router.php \
    && echo '    echo json_encode(["error" => "Endpoint not found"]);' >> /var/www/html/router.php \
    && echo '}' >> /var/www/html/router.php \
    && echo '?>' >> /var/www/html/router.php

# Expose port 80 (Railway will override with PORT env var)
EXPOSE 80

# Start both services
CMD ["/start.sh"]
