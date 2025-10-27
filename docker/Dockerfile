FROM php:8.3-fpm

# Установка зависимостей для PDO PostgreSQL и Composer
RUN apt-get update && apt-get install -y \
    libpq-dev \
    curl \
    && docker-php-ext-install pdo pdo_pgsql

# Установка Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Копируем composer.json для автозагрузки
COPY backend/composer.json /var/www/html/backend/composer.json

# Рабочая директория
WORKDIR /var/www/html

# Права на файлы (для dev)
RUN chown -R www-data:www-data /var/www/html

# Скрипт для init (Composer install если vendor отсутствует)
COPY docker/init.sh /usr/local/bin/init.sh
RUN chmod +x /usr/local/bin/init.sh

CMD ["/usr/local/bin/init.sh"]