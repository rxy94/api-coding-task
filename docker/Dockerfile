FROM php:8-fpm-alpine3.15 as base

RUN apk --update add \
    alpine-sdk \
    linux-headers \
    openssl-dev \
    php8-pear \
    php8-dev

RUN docker-php-ext-install pdo_mysql

RUN rm -rf /var/cache/apk/*

EXPOSE 9000

# --------------------------------------------------------------------------------------------------------------------

FROM base as development

ENV TZ ${TZ}

RUN pecl channel-update pecl.php.net

RUN apk add --update --upgrade tzdata autoconf g++ make \
    && ln -s /usr/share/zoneinfo/$TZ /etc/localtime \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer