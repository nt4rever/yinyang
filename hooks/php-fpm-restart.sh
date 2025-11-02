#!/usr/bin/env bash

sudo cp /usr/share/nginx/html/ec2/99-php.ini /etc/php.d/99-php.ini

sudo service php-fpm restart
