FROM php:8.0.5-fpm-alpine
WORKDIR /var/www/html
RUN docker-php-ext-install bcmath mysqli pdo pdo_mysql exif sockets
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer --version=2.0.1