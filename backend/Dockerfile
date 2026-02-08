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
    && chmod -R 755 /var/www/html \
    && touch /var/www/html/requests.log \
    && chown www-data:www-data /var/www/html/requests.log \
    && chmod 666 /var/www/html/requests.log

# Create basic nginx configuration that allows direct .php access
RUN echo 'server {' > /etc/nginx/sites-available/default \
    && echo '    listen 80;' >> /etc/nginx/sites-available/default \
    && echo '    root /var/www/html;' >> /etc/nginx/sites-available/default \
    && echo '    index index.php;' >> /etc/nginx/sites-available/default \
    && echo '' >> /etc/nginx/sites-available/default \
    && echo '    # CORS headers' >> /etc/nginx/sites-available/default \
    && echo '    add_header Access-Control-Allow-Origin "*" always;' >> /etc/nginx/sites-available/default \
    && echo '    add_header Access-Control-Allow-Methods "GET, POST, PUT, DELETE, OPTIONS" always;' >> /etc/nginx/sites-available/default \
    && echo '    add_header Access-Control-Allow-Headers "Content-Type, Authorization" always;' >> /etc/nginx/sites-available/default \
    && echo '' >> /etc/nginx/sites-available/default \
    && echo '    # Handle OPTIONS requests' >> /etc/nginx/sites-available/default \
    && echo '    if ($request_method = OPTIONS) {' >> /etc/nginx/sites-available/default \
    && echo '        return 200;' >> /etc/nginx/sites-available/default \
    && echo '    }' >> /etc/nginx/sites-available/default \
    && echo '' >> /etc/nginx/sites-available/default \
    && echo '    # Handle PHP files' >> /etc/nginx/sites-available/default \
    && echo '    location ~ \.php$ {' >> /etc/nginx/sites-available/default \
    && echo '        try_files $uri =404;' >> /etc/nginx/sites-available/default \
    && echo '        fastcgi_pass 127.0.0.1:9000;' >> /etc/nginx/sites-available/default \
    && echo '        fastcgi_index index.php;' >> /etc/nginx/sites-available/default \
    && echo '        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;' >> /etc/nginx/sites-available/default \
    && echo '        include fastcgi_params;' >> /etc/nginx/sites-available/default \
    && echo '    }' >> /etc/nginx/sites-available/default \
    && echo '}' >> /etc/nginx/sites-available/default

# Create supervisor configuration with stdout/stderr logging for Railway
RUN echo '[supervisord]' > /etc/supervisor/conf.d/supervisord.conf \
    && echo 'nodaemon=true' >> /etc/supervisor/conf.d/supervisord.conf \
    && echo 'silent=false' >> /etc/supervisor/conf.d/supervisord.conf \
    && echo 'logfile=/dev/stdout' >> /etc/supervisor/conf.d/supervisord.conf \
    && echo 'logfile_maxbytes=0' >> /etc/supervisor/conf.d/supervisord.conf \
    && echo '' >> /etc/supervisor/conf.d/supervisord.conf \
    && echo '[program:nginx]' >> /etc/supervisor/conf.d/supervisord.conf \
    && echo 'command=nginx -g "daemon off;"' >> /etc/supervisor/conf.d/supervisord.conf \
    && echo 'autostart=true' >> /etc/supervisor/conf.d/supervisord.conf \
    && echo 'autorestart=true' >> /etc/supervisor/conf.d/supervisord.conf \
    && echo 'priority=10' >> /etc/supervisor/conf.d/supervisord.conf \
    && echo 'stdout_logfile=/dev/stdout' >> /etc/supervisor/conf.d/supervisord.conf \
    && echo 'stdout_logfile_maxbytes=0' >> /etc/supervisor/conf.d/supervisord.conf \
    && echo 'stderr_logfile=/dev/stderr' >> /etc/supervisor/conf.d/supervisord.conf \
    && echo 'stderr_logfile_maxbytes=0' >> /etc/supervisor/conf.d/supervisord.conf \
    && echo '' >> /etc/supervisor/conf.d/supervisord.conf \
    && echo '[program:php-fpm]' >> /etc/supervisor/conf.d/supervisord.conf \
    && echo 'command=php-fpm --nodaemonize' >> /etc/supervisor/conf.d/supervisord.conf \
    && echo 'autostart=true' >> /etc/supervisor/conf.d/supervisord.conf \
    && echo 'autorestart=true' >> /etc/supervisor/conf.d/supervisord.conf \
    && echo 'priority=5' >> /etc/supervisor/conf.d/supervisord.conf \
    && echo 'stdout_logfile=/dev/stdout' >> /etc/supervisor/conf.d/supervisord.conf \
    && echo 'stdout_logfile_maxbytes=0' >> /etc/supervisor/conf.d/supervisord.conf \
    && echo 'stderr_logfile=/dev/stderr' >> /etc/supervisor/conf.d/supervisord.conf \
    && echo 'stderr_logfile_maxbytes=0' >> /etc/supervisor/conf.d/supervisord.conf

# Configure nginx to output logs to stdout/stderr for Railway
RUN ln -sf /dev/stdout /var/log/nginx/access.log \
    && ln -sf /dev/stderr /var/log/nginx/error.log

# Configure PHP-FPM to output logs to stderr for Railway
RUN echo '[global]' > /usr/local/etc/php-fpm.d/railway.conf \
    && echo 'error_log = /proc/self/fd/2' >> /usr/local/etc/php-fpm.d/railway.conf \
    && echo 'log_level = notice' >> /usr/local/etc/php-fpm.d/railway.conf \
    && echo '' >> /usr/local/etc/php-fpm.d/railway.conf \
    && echo '[www]' >> /usr/local/etc/php-fpm.d/railway.conf \
    && echo 'access.log = /proc/self/fd/2' >> /usr/local/etc/php-fpm.d/railway.conf \
    && echo 'catch_workers_output = yes' >> /usr/local/etc/php-fpm.d/railway.conf \
    && echo 'decorate_workers_output = no' >> /usr/local/etc/php-fpm.d/railway.conf

# Create startup script inline
RUN cat > /start.sh << 'EOF'
#!/bin/bash

echo "Starting PHP-FPM + Nginx with Supervisor for Railway..."

# Update nginx port for Railway if PORT environment variable is set
if [ ! -z "$PORT" ]; then
    echo "Railway PORT detected: $PORT"
    sed -i "s/listen 80;/listen $PORT;/" /etc/nginx/sites-available/default
else
    echo "Using default port 80"
fi

# Create necessary directories
mkdir -p /var/log/nginx
mkdir -p /var/run/nginx
mkdir -p /var/log/supervisor

# Ensure nginx log symlinks are correct for Railway
ln -sf /dev/stdout /var/log/nginx/access.log
ln -sf /dev/stderr /var/log/nginx/error.log

# Set proper permissions
chown -R www-data:www-data /var/www/html
chmod -R 755 /var/www/html

# Enable PHP error logging to stderr for Railway
echo "log_errors = On" >> /usr/local/etc/php/php.ini
echo "error_log = /proc/self/fd/2" >> /usr/local/etc/php/php.ini

# Start supervisor which will manage both PHP-FPM and Nginx
echo "Starting Supervisor to manage PHP-FPM and Nginx..."
echo "All logs will be output to stdout/stderr for Railway visibility"
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
EOF

RUN chmod +x /start.sh

# Router is no longer needed since nginx handles routing directly

# Expose port 80 (Railway will override with PORT env var)
EXPOSE 80

# Start both services
CMD ["/start.sh"]
