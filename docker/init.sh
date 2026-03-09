#!/bin/bash
set -e

echo "🔧 Инициализация проекта..."

echo "📦 Устанавливаем зависимости Composer ..."
cd backend || exit
if [ ! -d "vendor" ]; then
    composer install --no-progress --no-interaction
else
    composer update --no-progress --no-interaction
fi

# Запустить переданную команду (например, php-fpm)
exec "$@"

echo "🚀 Инициализация завершена!"
