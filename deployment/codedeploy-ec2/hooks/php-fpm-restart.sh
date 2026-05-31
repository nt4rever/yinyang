#!/usr/bin/env bash

sudo cp /usr/share/nginx/html/deployment/codedeploy-ec2/config/php/99-php.ini /etc/php.d/99-php.ini

sudo service php-fpm restart
