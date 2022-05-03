FROM php:8.1-fpm-buster

ARG ROUTER_NAME="cache_service"
ARG HOST_NAME="cache.diagro.farm"

RUN apt-get update
RUN apt install -y g++ libicu-dev libpq-dev libzip-dev zip zlib1g-dev curl
RUN docker-php-ext-install intl opcache pdo pdo_mysql

RUN apt-get install -y nginx
COPY docker/production/nginx/app.conf /etc/nginx/sites-enabled/default
COPY docker/production/entrypoint.sh /etc/entrypoint.sh
RUN chmod +x /etc/entrypoint.sh

RUN mkdir /diagro
COPY --chown=www-data:www-data app /diagro/app
COPY --chown=www-data:www-data bootstrap /diagro/bootstrap
COPY --chown=www-data:www-data config /diagro/config
COPY --chown=www-data:www-data database /diagro/database
COPY --chown=www-data:www-data public /diagro/public
COPY --chown=www-data:www-data resources /diagro/resources
COPY --chown=www-data:www-data routes /diagro/routes
COPY --chown=www-data:www-data storage /diagro/storage
COPY --chown=www-data:www-data .env.production /diagro/.env
COPY --chown=www-data:www-data composer.production.json /diagro/composer.json
COPY --chown=www-data:www-data artisan /diagro/artisan

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN chmod +x /usr/local/bin/composer

WORKDIR /diagro
RUN composer install --no-dev --optimize-autoloader

LABEL traefik.enable="true"
LABEL traefik.http.routers.$ROUTER_NAME.entrypoints="websecure"
LABEL traefik.http.routers.$ROUTER_NAME.rule="Host(`${HOST_NAME}`)"
LABEL traefik.http.routers.$ROUTER_NAME.tls="true"
LABEL traefik.http.routers.$ROUTER_NAME.tls.certresolver="production"

EXPOSE 80 443

ENTRYPOINT ["/etc/entrypoint.sh"]
