# Use the official PHP 8.x (or your required version) with Apache support
FROM php:8.2-apache

# Set working directory inside the container
WORKDIR /var/www/html

# Install system dependencies and PHP Extensions
RUN apt-get update && \
    apt-get install -y libzip-dev unzip && \
    docker-php-ext-install zip pdo pdo_mysql

# Enable Apache Rewrite Module
RUN a2enmod rewrite

# Copy application code to the container
COPY . /var/www/html

# Set proper permissions for web server access
RUN chown -R www-data:www-data /var/www/html && chmod -R 755 /var/www/html

# Install Composer (if your app uses it)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Run composer install to install PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# Expose port 80 to the outside world
EXPOSE 80

# Start the Apache server
CMD ["apache2-foreground"]