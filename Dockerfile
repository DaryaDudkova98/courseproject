FROM webdevops/php-nginx:8.3

# Устанавливаем только то, что реально нужно
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    libxml2-dev \
    curl

# Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Копируем проект
COPY . /var/www/html
WORKDIR /var/www/html

# Устанавливаем зависимости
RUN composer install --no-dev --optimize-autoloader

# Копируем конфиг nginx в правильное место
COPY docker/default.conf /opt/docker/etc/nginx/vhost.conf

# Права
RUN chown -R www-data:www-data /var/www/html

EXPOSE 8080
