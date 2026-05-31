#!/usr/bin/env bash

sudo service supervisord stop

# Laravel task scheduler
sudo systemctl stop laravel-schedule.timer

sudo service php-fpm stop

sudo service nginx stop
