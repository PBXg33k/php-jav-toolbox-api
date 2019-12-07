FROM pbxg33k/php-consumer-base AS base

FROM base AS build
RUN apk add --no-cache --update --virtual build-dependencies alpine-sdk git automake autoconf \
    && apk add --no-cache --update --virtual php-dependencies zlib-dev icu-dev libzip-dev re2c \
    && apk add --no-cache --update imagemagick git mysql-client wget mediainfo \
    && pecl install redis-4.0.2 \
	&& docker-php-ext-install opcache \
	&& docker-php-ext-install intl \
	&& docker-php-ext-install mbstring \
	&& docker-php-ext-install pdo_mysql \
	&& docker-php-ext-install zip \
	&& docker-php-ext-install bcmath \
	&& docker-php-ext-enable redis \
	&& apk del build-dependencies php-dependencies

RUN apk add --no-cache --update xxhash

FROM build AS final
WORKDIR /var/www

COPY . /var/www
WORKDIR /var/www/app
RUN composer install --no-dev --optimize-autoloader --prefer-dist --no-scripts

# Cleanup
RUN rm -rf /tmp/* && chmod +x /var/www/start.sh

CMD ["/var/www/start.sh"]
    
EXPOSE 9000
