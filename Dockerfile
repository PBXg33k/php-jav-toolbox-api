#FROM jrottenberg/ffmpeg:ubuntu AS ffmpegbuild

FROM php:7.2-fpm AS base

FROM base AS xxhbuild

WORKDIR /tmp/workdir

RUN apt-get update && apt-get install -y build-essential git

RUN git clone https://github.com/Cyan4973/xxHash.git \
    && cd xxHash \
    && make \
    && make install

FROM base AS final
MAINTAINER Oguzhan Uysal <development@oguzhanuysal.eu>

ENV LD_LIBRARY_PATH=/usr/local/lib

COPY --from=xxhbuild /usr/local /usr/local/

RUN apt-get update && apt-get install -y git zlib1g-dev libicu-dev libpq-dev imagemagick git mysql-client wget ffmpeg mediainfo \
	&& docker-php-ext-install opcache \
	&& docker-php-ext-install intl \
	&& docker-php-ext-install mbstring \
	&& docker-php-ext-install pdo_mysql \
	&& php -r "readfile('https://getcomposer.org/installer');" | php -- --install-dir=/usr/local/bin --filename=composer \
	&& chmod +sx /usr/local/bin/composer

RUN wget https://github.com/mutschler/mt/releases/download/1.0.8/mt-1.0.8-linux_amd64.tar.bz2 \
    && tar xvjf mt-1.0.8-linux_amd64.tar.bz2 \
    && mv mt-1.0.8-linux_amd64 /usr/local/bin/mt \
    && chmod +x /usr/local/bin/mt

RUN rm -rf /var/www/* \
    && git clone --no-checkout --depth=1 --no-tags \
       git@github.com:PBXg33k/php-jav-toolbox-api.git /var/www \
    && cd /var/www && composer install --no-dev --optimize-autoloader --prefer-dist

EXPOSE 9000