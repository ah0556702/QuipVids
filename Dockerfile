# Use PHP with Apache preinstalled
FROM php:8.2-apache

# Enable SQLite extension
RUN docker-php-ext-install pdo_sqlite

# Copy all your project files into the container
COPY . /var/www/html/

# Point Apache/PHP to serve from /public (safe web root)
WORKDIR /var/www/html/public

# Apache default port
EXPOSE 80
