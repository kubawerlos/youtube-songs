FROM php:8.4-cli-alpine

WORKDIR /app

COPY src src
COPY composer.* generate.php ./
COPY --from=composer/composer:2-bin /composer /usr/bin/composer

RUN composer install --no-dev --no-progress

ENTRYPOINT [ "php", "/app/generate.php" ]
