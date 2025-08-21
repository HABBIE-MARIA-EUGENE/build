# Start from official PHP + Apache image
FROM php:8.2-apache

# Install system dependencies needed for MongoDB extension
RUN apt-get update && apt-get install -y \
    libssl-dev \
    pkg-config \
    unzip \
    git \
    && pecl install mongodb \
    && docker-php-ext-enable mongodb \
    && rm -rf /var/lib/apt/lists/*

# Enable Apache rewrite module (if you need it for routing)
RUN a2enmod rewrite
# Set working directory inside container
WORKDIR /var/www/html/

# Copy project files into container
COPY . /var/www/html/

# Copy composer from official composer image
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
# Copy project files (use public as DocumentRoot)
COPY public/ /var/www/html/



# Set Apache ServerName to avoid warnings
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Install PHP dependencies (now ext-mongodb is available)
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# Expose port 80
EXPOSE 80
