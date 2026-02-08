FROM php:8.2-fpm

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

# Create nginx configuration with clean URLs
RUN cat > /etc/nginx/sites-available/default << 'EOF'
server {
    listen 80;
    root /var/www/html;
    index index.php;

    # Configure logs for Railway - output to stdout/stderr
    access_log /dev/stdout combined;
    error_log /dev/stderr warn;

    # CORS headers
    add_header Access-Control-Allow-Origin "*" always;
    add_header Access-Control-Allow-Methods "GET, POST, PUT, DELETE, OPTIONS" always;
    add_header Access-Control-Allow-Headers "Content-Type, Authorization, X-Requested-With" always;

    # Handle preflight OPTIONS requests
    if ($request_method = OPTIONS) {
        return 200;
    }

    # Health check endpoint - don't log these for cleaner logs
    location /health {
        access_log off;
        return 200 "healthy\n";
        add_header Content-Type text/plain;
    }

    # Clean URL rewrites for API endpoints
    location /api { try_files $uri /api_info.php; }
    location /info { try_files $uri /api_info.php; }
    location /swagger { try_files $uri /swagger.php; }
    location /docs { try_files $uri /swagger.php; }
    location /swagger.json { try_files $uri /swagger_json.php; }
    location /logs { try_files $uri /logs.php; }
    location /db { try_files $uri /db_test.php; }
    location /test-logs { try_files $uri /log_test.php; }

    # Main API endpoints (clean URLs)
    location /Login { try_files $uri /Login.php; }
    location /Register { try_files $uri /Register.php; }
    location /getContacts { try_files $uri /getContacts.php; }
    location /addContact { try_files $uri /addContact.php; }
    location /updateContact { try_files $uri /updateContact.php; }
    location /deleteContact { try_files $uri /deleteContact.php; }
    location /editContact { try_files $uri /editContact.php; }
    location /searchContacts { try_files $uri /searchContacts.php; }

    # Handle direct .php access and all other requests
    location ~ \.php$ {
        try_files $uri =404;
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_param REQUEST_URI $request_uri;
        fastcgi_param QUERY_STRING $query_string;
        fastcgi_param REQUEST_METHOD $request_method;
        fastcgi_param CONTENT_TYPE $content_type;
        fastcgi_param CONTENT_LENGTH $content_length;
        include fastcgi_params;
    }

    # Deny access to sensitive files
    location ~ /\. { deny all; }
    location ~ \.(env|log)$ { deny all; }
}
EOF

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

# Configure PHP error logging to stderr for Railway
RUN echo "log_errors = On" >> /usr/local/etc/php/php.ini \
    && echo "error_log = /proc/self/fd/2" >> /usr/local/etc/php/php.ini \
    && echo "display_errors = Off" >> /usr/local/etc/php/php.ini

# Create supervisor configuration with stdout/stderr logging for Railway
RUN cat > /etc/supervisor/conf.d/supervisord.conf << 'EOF'
[supervisord]
nodaemon=true
silent=false
logfile=/dev/stdout
logfile_maxbytes=0

[program:nginx]
command=nginx -g "daemon off;"
autostart=true
autorestart=true
priority=10
stdout_logfile=/dev/stdout
stdout_logfile_maxbytes=0
stderr_logfile=/dev/stderr
stderr_logfile_maxbytes=0

[program:php-fpm]
command=php-fpm --nodaemonize
autostart=true
autorestart=true
priority=5
stdout_logfile=/dev/stdout
stdout_logfile_maxbytes=0
stderr_logfile=/dev/stderr
stderr_logfile_maxbytes=0
EOF

# Create startup script for Railway
RUN cat > /start.sh << 'EOF'
#!/bin/bash

echo "Starting nginx + PHP-FPM with Supervisor for Railway..."

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

# Start supervisor which will manage both PHP-FPM and Nginx
echo "Starting Supervisor to manage PHP-FPM and Nginx..."
echo "All logs will be output to stdout/stderr for Railway visibility"
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
EOF

RUN chmod +x /start.sh

# Expose port (Railway will override with PORT env var)
EXPOSE 80

# Start both services
CMD ["/start.sh"]
