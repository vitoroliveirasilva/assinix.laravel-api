FROM php:8.4-fpm-alpine

WORKDIR /var/www/html

RUN apk add --no-cache \
    bash \
    curl \
    git \
    icu-dev \
    libzip-dev \
    linux-headers \
    oniguruma-dev \
    postgresql-dev \
    unzip \
    zip \
    $PHPIZE_DEPS

RUN docker-php-ext-install \
    bcmath \
    intl \
    mbstring \
    opcache \
    pcntl \
    pdo_pgsql \
    zip

RUN pecl install redis \
    && docker-php-ext-enable redis

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY docker/php/php.ini /usr/local/etc/php/conf.d/99-assinix.ini

CMD ["php-fpm"]