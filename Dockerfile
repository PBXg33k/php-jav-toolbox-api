FROM php:7.2-fpm AS base

FROM base AS xxhbuild

WORKDIR /tmp/workdir

RUN apt-get update \
    && apt-get install -y build-essential git \
    && git clone https://github.com/Cyan4973/xxHash.git \
    && cd xxHash \
    && make \
    && make install \
    && apt-get remove --auto-remove -y build-essential


FROM base AS final
MAINTAINER Oguzhan Uysal <development@oguzhanuysal.eu>

WORKDIR /var/www

# Copy compiled xxhsum from xxhbuild container
COPY --from=xxhbuild /usr/local /usr/local/

# install PHP extensions & composer
RUN apt-get update && apt-get install -y git \
    zlib1g-dev libicu-dev libpq-dev imagemagick git mysql-client wget mediainfo \
    && pecl install redis-4.0.2 \
	&& docker-php-ext-install opcache \
	&& docker-php-ext-install intl \
	&& docker-php-ext-install mbstring \
	&& docker-php-ext-install pdo_mysql \
	&& docker-php-ext-install zip \
	&& docker-php-ext-enable redis \
	&& php -r "readfile('https://getcomposer.org/installer');" | php -- --install-dir=/usr/local/bin --filename=composer \
	&& chmod +sx /usr/local/bin/composer

# install MT (media thumbnails)
RUN wget https://github.com/mutschler/mt/releases/download/1.0.8/mt-1.0.8-linux_amd64.tar.bz2 \
    && tar xvjf mt-1.0.8-linux_amd64.tar.bz2 \
    && mv mt-1.0.8-linux_amd64 /usr/local/bin/mt \
    && chmod +x /usr/local/bin/mt

# Authorize SSH Host
RUN mkdir -p /root/.ssh \
    && chmod 0700 /root/.ssh \
    && ssh-keyscan github.com > /root/.ssh/known_hosts

# Add the keys and set permissions

RUN ssh-keygen -q -t rsa -N '' -f /root/.ssh/id_rsa \
    && chmod 600 /root/.ssh/id_rsa \
    && chmod 600 /root/.ssh/id_rsa.pub

RUN rm -rf /var/www/* \
    && git clone https://github.com/PBXg33k/php-jav-toolbox-api.git /var/www

WORKDIR /var/www/app

RUN composer install --no-dev --optimize-autoloader --prefer-dist --no-scripts

RUN rm -rf /tmp/*
    
EXPOSE 9000