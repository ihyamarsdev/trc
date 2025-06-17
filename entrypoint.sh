#!/bin/sh

php artisan optimize:clear
php artisan key:generate
php artisan migrate --force
php artisan storage:link
php artisan optimize