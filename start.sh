#!/usr/bin/env sh
composer install && /var/www/app/bin/console doctrine:migrations:migrate --no-interaction && php-fpm
