FROM php:8.0-apache

RUN apt-get update -y \
    && apt-get install -y --no-install-recommends \
        autoconf \
        git \
        libcurl4-openssl-dev \
        libicu-dev \
        libgmp-dev \
        libmagic-dev \
        libonig-dev \
        libedit-dev \
        libreadline-dev \
        libxml2-dev \
        sqlite3 \
        unzip \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install -j$(nproc) \
        curl \
        exif \
        gmp \
        intl \
        mbstring \
        pcntl \
        pdo_mysql \
        readline \
    && pecl install ast \
    && pecl install solr

RUN a2enmod rewrite

COPY --from=composer /usr/bin/composer /usr/bin/composer
