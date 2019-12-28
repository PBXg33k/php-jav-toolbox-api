#!/usr/bin/env sh
composer install \
 && /var/www/app/bin/console messenger:setup-transports \
 && /var/www/app/bin/console doctrine:migrations:migrate --no-interaction \
 && nginx \
 && php-fpm
