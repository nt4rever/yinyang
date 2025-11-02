#!/usr/bin/env bash

sudo cp /usr/share/nginx/html/ec2/supervisord.laravel.conf /etc/supervisor/conf.d/supervisord.laravel.conf

sudo service supervisord restart

# Laravel task scheduler
sudo systemctl restart laravel-schedule.timer
