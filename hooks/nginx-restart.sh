#!/usr/bin/env bash

sudo cp /usr/share/nginx/html/laravel/deployment/aws/nginx.default.conf /etc/nginx/conf.d/default.conf

sudo service nginx restart
