#!/usr/bin/env bash

sudo -u nginx php /usr/share/nginx/html/laravel/artisan optimize:clear
sudo -u nginx php /usr/share/nginx/html/laravel/artisan optimize
sudo -u nginx php /usr/share/nginx/html/laravel/artisan migrate --isolated --force
