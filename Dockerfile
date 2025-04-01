FROM composer/composer:2.5 as composer
FROM unit:1.34.1-php8.3 as unit

COPY --from=composer /usr/bin/composer /usr/bin/composer
ENV COMPOSER_HOME="/tmp/composer"

RUN set -x  \
    && apt-get update  \
    && apt-get install -y  \
    nano \
    libpq-dev \
    zlib1g-dev \
    libpng-dev \
    libzip-dev  \
    zip \
    libicu-dev

RUN docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql
RUN docker-php-ext-install \
    pdo \
    pdo_pgsql \
    gd \
    sockets \
    zip \
    intl

COPY ./config.json /docker-entrypoint.d/

WORKDIR /app

COPY composer.* ./
RUN composer install -n --no-cache --no-ansi --no-autoloader --no-scripts --prefer-dist

COPY . .

RUN set -x  \
    && composer dump-autoload -n --optimize  \
    && chmod -R 777 /app/storage \
    && php ./artisan storage:link \
    && php ./artisan filament:optimize-clear

EXPOSE 80
