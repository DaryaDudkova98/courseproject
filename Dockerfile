FROM php:8.3-fpm

RUN apt-get update && apt-get install -y \
    git unzip libicu-dev libzip-dev libpng-dev libonig-dev \
    && docker-php-ext-install intl pdo pdo_mysql zip opcache

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .

RUN composer install --no-dev --optimize-autoloader

RUN apt-get install -y nginx

COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf


ENV PORT=8080

CMD service nginx start && php-fpm
