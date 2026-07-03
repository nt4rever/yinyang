#!/usr/bin/env bash
set -e

DC_RUN_ARGS="-f docker-compose.yml"

if ! command -v docker &>/dev/null; then
    echo "Docker is not installed."
    exit 1
fi

[ ! -f .env ] && cp .env.example .env

DOCKER_BUILDKIT=1 docker buildx bake --load

docker compose ${DC_RUN_ARGS} run --rm laravel composer install --no-interaction --prefer-dist
docker compose ${DC_RUN_ARGS} run --rm laravel npm install

docker compose ${DC_RUN_ARGS} up -d --remove-orphans

if grep -q '^APP_KEY=$' .env 2>/dev/null || ! grep -q '^APP_KEY=base64:' .env 2>/dev/null; then
    docker compose ${DC_RUN_ARGS} exec -T laravel php artisan key:generate --force
fi

docker compose ${DC_RUN_ARGS} exec -T laravel php artisan migrate --isolated --force || \
docker compose ${DC_RUN_ARGS} exec -T laravel php artisan migrate --force

echo "Application started at http://localhost:8000"
