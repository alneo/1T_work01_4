#!/bin/sh

php artisan key:generate
php artisan optimize
php artisan make:queue-table
php artisan migrate

# Запуск приложения
supervisord -c "/etc/supervisor.d/supervisord.ini"
