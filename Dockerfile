FROM php:8.3-cli-alpine

WORKDIR /app

COPY src src
COPY composer.* generate.php ./
COPY --from=composer/composer:2-bin /composer /usr/bin/composer

RUN apk add --update yaml-dev \
 && apk add --no-cache --virtual .build-deps g++ make autoconf \
 && pecl channel-update pecl.php.net \
 && pecl install yaml \
 && docker-php-ext-enable yaml \
 && composer install --no-dev --no-progress

ENTRYPOINT [ "php", "/app/generate.php" ]
