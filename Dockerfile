# Base image with PHP and Apache
FROM php:8.2-apache

# Install system dependencies for PHP extensions
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    git \
    curl \
    && docker-php-ext-install pdo_mysql mysqli

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Copy project files into container
COPY . /var/www/html/

# Set working directory
WORKDIR /var/www/html/

# Install composer (for vendor autoload)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
RUN composer install

# Expose Apache port
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]
