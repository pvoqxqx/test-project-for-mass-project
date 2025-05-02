FROM php:8.1-fpm

RUN apt-get update && apt-get install -y libpq-dev git unzip libjpeg-dev libpng-dev libfreetype6-dev && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-configure gd --with-freetype --with-jpeg && \
    docker-php-ext-install gd pdo pdo_pgsql

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /var/www

COPY . .

# Устанавливаем зависимости проекта через Composer
RUN composer install --no-scripts --no-autoloader

EXPOSE 9000

CMD ["php-fpm"]
