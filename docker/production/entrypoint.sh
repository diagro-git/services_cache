#!/usr/bin/env bash
php artisan queue:work &
service nginx start
php-fpm
