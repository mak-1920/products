FROM php:8.0-fpm

RUN apt-get update \
  && apt-get install -y \
             apt-utils \
             icu-devtools \
             libicu-dev \
             libjpeg62-turbo-dev \
             libpng-dev \
             libxml2-dev \
             libzip-dev \
             libpq-dev \
             libfreetype6-dev \
    && pecl install xdebug \
    && docker-php-ext-configure intl --enable-intl && \
    docker-php-ext-configure gd --with-freetype --with-jpeg && \
    docker-php-ext-install -j$(nproc) gd && \
    docker-php-ext-install \
        pgsql pdo_pgsql \
        mysqli pdo_mysql \
        intl \
        zip pcntl \
        exif opcache \
        sockets \
    && docker-php-ext-enable xdebug \
    && docker-php-source delete
