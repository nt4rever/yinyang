#!/usr/bin/env bash

sudo php /usr/share/nginx/html/artisan optimize:clear
sudo php /usr/share/nginx/html/artisan optimize
sudo chown -R 777 /usr/share/nginx/html/storage /usr/share/nginx/html/bootstrap/cache
sudo php /usr/share/nginx/html/artisan migrate --isolated --force
