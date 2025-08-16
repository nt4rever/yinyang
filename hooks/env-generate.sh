#!/usr/bin/env bash

environment=`env | grep APPLICATION_NAME | cut -c 18-20`

sudo rm -f /usr/share/nginx/html/laravel/.env
sudo aws ssm get-parameters-by-path --region ap-southeast-1 --path "/${environment}/ec2-web" --with-decryption | jq -r '.Parameters | map([.Name[13:], .Value] | join("=")) | join("\n")' | grep -v PASSWORD_RULE | sudo tee /usr/share/nginx/html/laravel/.env > /dev/null 2>&1
sudo aws ssm get-parameters-by-path --region ap-southeast-1 --path "/${environment}/ec2-web" --with-decryption | jq -r '.Parameters | map([.Name[13:], .Value] | join("=")) | join("\n")' | grep PASSWORD_RULE | sudo tee -a /usr/share/nginx/html/laravel/.env > /dev/null 2>&1
