FROM php:7.3-fpm-alpine AS base

FROM base AS buildbase
RUN apk add --no-cache --update --virtual build-dependencies alpine-sdk git automake autoconf

FROM buildbase AS build
MAINTAINER Oguzhan Uysal <development@oguzhanuysal.eu>

# install PHP extensions & composer
RUN apk add --no-cache --update --virtual php-dependencies zlib-dev icu-dev libzip-dev \
    && apk add --no-cache --update imagemagick git mysql-client wget mediainfo \
    && pecl install redis-4.0.2 \
	&& docker-php-ext-install opcache \
	&& docker-php-ext-install intl \
	&& docker-php-ext-install mbstring \
	&& docker-php-ext-install pdo_mysql \
	&& docker-php-ext-install zip \
	&& docker-php-ext-install bcmath \
	&& docker-php-ext-enable redis \
	&& php -r "readfile('https://getcomposer.org/installer');" | php -- --install-dir=/usr/local/bin --filename=composer \
	&& chmod +sx /usr/local/bin/composer

# Compile and install php amqp extension
RUN apk add --no-cache --update rabbitmq-c rabbitmq-c-dev \
    && git clone --depth=1 https://github.com/pdezwart/php-amqp.git /tmp/php-amqp \
    && cd /tmp/php-amqp \
    && phpize && ./configure && make && make install \
    && cd ../ && rm -rf /tmp/php-amqp \
    && docker-php-ext-enable amqp

# install MT (media thumbnails)
RUN wget https://github.com/mutschler/mt/releases/download/1.0.8/mt-1.0.8-linux_amd64.tar.bz2 \
    && tar xvjf mt-1.0.8-linux_amd64.tar.bz2 \
    && mv mt-1.0.8-linux_amd64 /usr/local/bin/mt \
    && chmod +x /usr/local/bin/mt \
    && rm -f mt-1.0.8-linux_amd64.tar.bz2

FROM build AS final
WORKDIR /var/www

COPY . /var/www
WORKDIR /var/www/app
RUN apk add --no-cache --update ffmpeg xxhash \
    && composer install --no-dev --optimize-autoloader --prefer-dist --no-scripts

# Cleanup
RUN rm -rf /tmp/*
    
EXPOSE 9000
