# syntax=docker/dockerfile:1

FROM php:8.2-cli

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

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . /var/www/html/

# Create a non-root user
RUN groupadd -g 1000 www && \
    useradd -u 1000 -ms /bin/bash -g www www

# Change ownership of the working directory
RUN chown -R www:www /var/www/html

# Switch to non-root user
USER www

# Expose the port (Railway will set PORT environment variable)
EXPOSE 8000

# Start PHP built-in server with router
CMD ["sh", "-c", "cd /var/www/html && php -S 0.0.0.0:${PORT:-9000} router.php"]
