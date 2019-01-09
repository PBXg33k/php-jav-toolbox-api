#!/usr/bin/env sh

/var/www/app/bin/console doctrine:migrations:migrate --no-interaction && php-fpm
