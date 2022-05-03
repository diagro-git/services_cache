#!/usr/bin/env bash
composer install

php artisan queue:work &

service nginx start
php-fpm
