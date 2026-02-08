#!/bin/bash

echo "Starting PHP-FPM + Nginx with Supervisor..."

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

# Set proper permissions
chown -R www-data:www-data /var/www/html
chmod -R 755 /var/www/html

# Start supervisor which will manage both PHP-FPM and Nginx
echo "Starting Supervisor to manage PHP-FPM and Nginx..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
