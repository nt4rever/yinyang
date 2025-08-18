#!/usr/bin/env bash

source /etc/environment

sudo service supervisord stop

sudo cp /usr/share/nginx/html/laravel/deployment/aws/supervisord.laravel.conf /etc/supervisor/conf.d/supervisord.laravel.conf

echo "WITH_WORKER value is: ${WITH_WORKER}"
echo "WITH_CRON value is: ${WITH_CRON}"

sudo sed -i "s|%(ENV_WITH_WORKER)s|${WITH_WORKER:-true}|g" /etc/supervisor/conf.d/supervisord.laravel.conf

sudo service supervisord start

if [ "${WITH_CRON:-true}" = "true" ]; then
    sudo systemctl restart laravel-schedule.timer
fi
