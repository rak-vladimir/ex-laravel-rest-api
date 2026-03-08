FROM php:8.4-fpm-alpine

ARG UID
ARG GID

RUN set -eux; \
    apk add --no-cache --virtual .build-deps \
        $PHPIZE_DEPS \
        postgresql-dev \
        libzip-dev \
        oniguruma-dev \
        libxml2-dev \
        zlib-dev \
    && docker-php-ext-install \
        pdo \
        pdo_pgsql \
        mbstring \
        xml \
        zip \
        bcmath \
        pcntl \
    && runDeps="$( \
        scanelf --needed --nobanner --format '%n#p' --recursive /usr/local/lib/php/extensions \
        | tr ',' '\n' \
        | sort -u \
        | awk 'system("[ -e /usr/local/lib/" $1 " ]") == 0 { next } { print "so:" $1 }' \
    )" \
    && apk add --no-cache --virtual .phpext-rundeps $runDeps \
    && apk del .build-deps \
    && rm -rf /var/cache/apk/* /tmp/* /var/tmp/*

COPY --from=composer:2.9.5 /usr/bin/composer /usr/bin/composer

RUN addgroup -g ${GID} app \
    && adduser -D -u ${UID} -G app app

WORKDIR /var/www/html

COPY _docker/php/local.ini /usr/local/etc/php/conf.d/local.ini

USER app

EXPOSE 9000
CMD ["php-fpm"]
