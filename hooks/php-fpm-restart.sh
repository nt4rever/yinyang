#!/usr/bin/env bash
set -e

sudo cp /usr/share/nginx/html/laravel/deployment/aws/99-php.ini /etc/php.d/99-php.ini

sudo service php-fpm restart
