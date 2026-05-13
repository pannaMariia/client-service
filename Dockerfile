FROM php:8.2-fpm

#  зависимости
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \
    libpng-dev \
    zip \
    unzip \
    git \
    curl

#  расширения PHP
RUN docker-php-ext-install pdo_pgsql pgsql bcmath gd zip

#  Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Рабочая директория
WORKDIR /var/www/html

# Копируем файлы проекта
COPY . .

#  зависимости Composer
RUN composer install --optimize-autoloader --no-dev

# Права на storage
RUN chown -R www-data:www-data storage bootstrap/cache
RUN chmod -R 775 storage bootstrap/cache

# Создаём папку для логов если нет
RUN mkdir -p storage/logs && chmod -R 775 storage/logs

EXPOSE 8000

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]

