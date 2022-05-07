#!/usr/bin/env bash
composer update

php artisan queue:work &

service nginx start
php-fpm
