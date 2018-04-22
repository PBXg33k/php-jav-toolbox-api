FROM jrottenberg/ffmpeg:ubuntu AS ffmpegbuild

FROM debian:stretch-slim AS xxhbuild

WORKDIR /tmp/workdir

RUN apt-get update && apt-get install -y build-essential git

RUN git clone https://github.com/Cyan4973/xxHash.git \
    && cd xxHash \
    && make \
    && make install

FROM php:7.2-fpm
MAINTAINER Oguzhan Uysal <development@oguzhanuysal.eu>

COPY --from=ffmpegbuild /usr/local /usr/local/
COPY --from=xxhbuild /usr/local /usr/local/

RUN apt-get update && apt-get install -y git zlib1g-dev libicu-dev libpq-dev imagemagick git mysql-client\
	&& docker-php-ext-install opcache \
	&& docker-php-ext-install intl \
	&& docker-php-ext-install mbstring \
	&& docker-php-ext-install pdo_mysql \
	&& php -r "readfile('https://getcomposer.org/installer');" | php -- --install-dir=/usr/local/bin --filename=composer \
	&& chmod +sx /usr/local/bin/composer

EXPOSE 9000