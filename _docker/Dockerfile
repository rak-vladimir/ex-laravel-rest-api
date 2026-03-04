FROM php:8.4-fpm-alpine

ARG UID
ARG GID

RUN apk add --no-cache \
    bash \
    curl \
    libpq \
    libzip-dev \
    zlib-dev \
    postgresql-dev \
    oniguruma-dev \
    libxml2-dev

RUN docker-php-ext-install \
    pdo \
    pdo_pgsql \
    mbstring \
    xml \
    zip \
    bcmath \
    pcntl

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN addgroup -g ${GID} app \
    && adduser -D -u ${UID} -G app app

WORKDIR /var/www/html

COPY _docker/php/local.ini /usr/local/etc/php/conf.d/local.ini

USER app

EXPOSE 9000
CMD ["php-fpm"]
