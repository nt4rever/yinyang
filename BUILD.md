# Build instructions

### This file describes how to build the Docker image for the project.

### Prerequisites

- Docker (latest stable). For best results enable BuildKit (or use docker buildx).

### Recommended (BuildKit enabled, uses cache mounts present in Dockerfile)

```bash
DOCKER_BUILDKIT=1 docker build \
  --pull \
  -f docker/octane/FrankenPHP.Dockerfile \
  --build-arg PHP_VERSION=8.4 \
  --build-arg FRANKENPHP_VERSION=1.9 \
  --build-arg COMPOSER_VERSION=2.8 \
  --build-arg USER_ID=1000 \
  --build-arg GROUP_ID=1000 \
  -t yinyang/frankenphp:php8.4 \
  .
```

or with php-pfm

```bash
DOCKER_BUILDKIT=1 docker build \
  --pull \
  -f docker/php-fpm/Dockerfile \
  --build-arg PHP_VERSION=8.4 \
  --build-arg COMPOSER_VERSION=2.8 \
  --build-arg USER_ID=1000 \
  --build-arg GROUP_ID=1000 \
  -t yinyang/php-fpm:php8.4 \
  .
```

### Minimal (no BuildKit features)

```bash
DOCKER_BUILDKIT=0 docker build \
  -f docker/octane/FrankenPHP.Dockerfile \
  -t yinyang/frankenphp:php8.4 \
  .
```

or with php-fpm

```bash
DOCKER_BUILDKIT=0 docker build \
  -f docker/php-fpm/Dockerfile \
  -t yinyang/php-fpm:php8.4 \
  .
```

### Optional faster iterative build with buildx + local cache

```bash
DOCKER_BUILDKIT=1 docker buildx build --load \
  --pull \
  -f docker/octane/FrankenPHP.Dockerfile \
  --build-arg PHP_VERSION=8.4 \
  --build-arg FRANKENPHP_VERSION=1.9 \
  --build-arg COMPOSER_VERSION=2.8 \
  --build-arg USER_ID=1000 \
  --build-arg GROUP_ID=1000 \
  --cache-to=type=local,dest=/tmp/docker-cache \
  --cache-from=type=local,src=/tmp/docker-cache \
  -t yinyang/frankenphp:php8.4 \
  .
```

or with php-fpm

```bash
DOCKER_BUILDKIT=1 docker buildx build --load \
  --pull \
  -f docker/php-fpm/Dockerfile \
  --build-arg PHP_VERSION=8.4 \
  --build-arg COMPOSER_VERSION=2.8 \
  --build-arg USER_ID=1000 \
  --build-arg GROUP_ID=1000 \
  --cache-to=type=local,dest=/tmp/docker-cache \
  --cache-from=type=local,src=/tmp/docker-cache \
  -t yinyang/php-fpm:php8.4 \
  .
```

### Run example

Run container (exposes ports 8000, 2019, 8080):

```bash
  docker run --rm -p 8000:8000 -p 2019:2019 -p 8080:8080 yinyang/frankenphp:php8.4
```
