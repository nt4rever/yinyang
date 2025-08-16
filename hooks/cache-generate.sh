#!/usr/bin/env bash

sudo chmod -R 777 /usr/share/nginx/html/laravel/storage
sudo chmod -R 777 /usr/share/nginx/html/laravel/bootstrap/cache
sudo php /usr/share/nginx/html/laravel/artisan optimize:clear
sudo php /usr/share/nginx/html/laravel/artisan optimize
sudo php /usr/share/nginx/html/laravel/artisan migrate --isolated --force
