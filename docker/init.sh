#!/bin/bash

# Проверяем, есть ли vendor/ в backend
if [ ! -d "/var/www/html/backend/vendor" ]; then
    echo "Installing Composer dependencies..."
    cd /var/www/html/backend && composer install --no-dev --optimize-autoloader
else
    echo "Composer dependencies already installed."
fi

# Запускаем php-fpm
exec php-fpm