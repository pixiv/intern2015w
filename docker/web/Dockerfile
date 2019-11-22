FROM php:7.1-apache

RUN apt-get update -y \
    && apt-get install -y --no-install-recommends \
        autoconf \
        git \
        libcurl4-openssl-dev \
        libicu-dev \
        libgmp-dev \
        libmagic-dev \
        libedit-dev \
        libreadline-dev \
        libxml2-dev \
        sqlite3 \
        unzip \
    && rm -rf /var/lib/apt/lists/*

RUN git clone https://github.com/nikic/php-ast.git \
    && cd php-ast \
    && phpize \
    && ./configure \
    && make install \
    && echo 'extension=ast.so' > /usr/local/etc/php/php.ini \
    && cd .. && rm -rf php-ast

RUN docker-php-ext-install -j$(nproc) \
        curl \
        exif \
        gmp \
        intl \
        mbstring \
        pcntl \
        pdo_mysql \
        readline \
    && pecl install solr

RUN a2enmod rewrite

COPY --from=composer /usr/bin/composer /usr/bin/composer
