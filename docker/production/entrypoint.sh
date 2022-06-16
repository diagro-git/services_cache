#!/usr/bin/env bash
php artisan queue:work --queue="store" &
php artisan queue:work --queue="remove" &
php artisan queue:work --queue="remove_user" &
php artisan queue:work --queue="remove_company" &

service nginx start
php-fpm
