#!/usr/bin/env bash
set -e

php artisan ide-helper:generate
php artisan ide-helper:meta
php artisan ide-helper:models --nowrite
