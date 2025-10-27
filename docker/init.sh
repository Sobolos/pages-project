#!/bin/bash
set -e

# Перейти в рабочую директорию
cd /var/www/html/backend

# Если нет vendor — установить зависимости
if [ ! -d "vendor" ]; then
    composer install --no-progress --no-interaction
fi

# Запустить переданную команду (например, php-fpm)
exec "$@"