#!/usr/bin/env bash

sudo cp /usr/share/nginx/html/cloudformation/config/nginx.default.conf /etc/nginx/conf.d/default.conf
sudo service nginx restart
