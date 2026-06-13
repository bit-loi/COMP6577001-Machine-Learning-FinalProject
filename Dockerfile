# Use official PHP image with Apache
FROM php:8.0-apache

# Set working directory
WORKDIR /var/www/html

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_mysql mysqli mbstring exif pcntl bcmath gd \
    && pecl install redis \
    && docker-php-ext-enable redis

# Enable Apache mod_rewrite
RUN a2dismod -q mpm_event mpm_worker || true \
    && a2enmod -q mpm_prefork rewrite \
    && echo 'ServerName localhost' > /etc/apache2/conf-available/servername.conf \
    && a2enconf -q servername

COPY docker-entrypoint.sh /usr/local/bin/shopmart-start
RUN chmod +x /usr/local/bin/shopmart-start

# Copy application files
COPY . /var/www/html/

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Configure Apache to allow .htaccess and listen on an unprivileged port
RUN sed -i 's/Listen 80/Listen 8080/' /etc/apache2/ports.conf \
    && sed -i 's/<VirtualHost \*:80>/<VirtualHost *:8080>/' /etc/apache2/sites-available/000-default.conf \
    && echo '<Directory /var/www/html/>\n\
    Options Indexes FollowSymLinks\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>' > /etc/apache2/conf-available/shopmart.conf \
    && a2enconf shopmart \
    && chown -R www-data:www-data /var/lock/apache2 /var/log/apache2 /var/run/apache2

# Expose the unprivileged Apache port
EXPOSE 8080

# Start Apache in foreground
CMD ["shopmart-start"]
