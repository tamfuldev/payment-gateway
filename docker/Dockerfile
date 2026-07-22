# PHP toolchain image for developing and testing the package (CLI only — this is a library).
FROM php:8.3-cli-alpine

# System deps + PHP extensions required by Composer, PHPUnit/Pest and Laravel Testbench.
# pcov is used for fast code coverage (lighter than Xdebug).
RUN apk add --no-cache git unzip libzip-dev oniguruma-dev \
    && docker-php-ext-install -j"$(nproc)" mbstring zip bcmath \
    && apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
    && pecl install pcov \
    && docker-php-ext-enable pcov \
    && apk del .build-deps

# Composer from the official image.
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

ENV COMPOSER_ALLOW_SUPERUSER=1

WORKDIR /app
