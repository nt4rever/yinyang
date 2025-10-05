#!/usr/bin/env bash

sudo cp /usr/share/nginx/html/deployment/aws/supervisord.laravel.conf /etc/supervisor/conf.d/supervisord.laravel.conf

sudo service supervisord restart

# Laravel task scheduler
sudo systemctl restart laravel-schedule.timer
