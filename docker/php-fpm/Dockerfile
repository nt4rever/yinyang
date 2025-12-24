# syntax=docker/dockerfile:1.4
ARG PHP_VERSION=8.4
ARG COMPOSER_VERSION=2.8

FROM composer:${COMPOSER_VERSION} AS vendor

FROM php:${PHP_VERSION}-fpm

ARG USER_ID=1000
ARG GROUP_ID=1000
ARG TZ=UTC

ENV DEBIAN_FRONTEND=noninteractive \
    TERM=xterm-color \
    TZ=${TZ} \
    USER=laravel \
    ROOT=/var/www/html \
    # APP_ENV=production \
    COMPOSER_FUND=0 \
    COMPOSER_MAX_PARALLEL_HTTP=48 \
    WITH_HORIZON=false \
    WITH_SCHEDULER=false \
    WITH_REVERB=false

# ENV XDG_CONFIG_HOME=${ROOT}/.config XDG_DATA_HOME=${ROOT}/.data

WORKDIR ${ROOT}

SHELL ["/bin/bash", "-eou", "pipefail", "-c"]

RUN ln -snf /usr/share/zoneinfo/${TZ} /etc/localtime \
    && echo ${TZ} > /etc/timezone

RUN echo "Acquire::http::No-Cache true;" >> /etc/apt/apt.conf.d/99custom && \
    echo "Acquire::BrokenProxy    true;" >> /etc/apt/apt.conf.d/99custom

RUN apt-get update; \
    apt-get upgrade -yqq; \
    apt-get install -yqq --no-install-recommends --show-progress \
    apt-utils \
    curl \
    nano \
    git \
    unzip \
    procps \
    ca-certificates \
    supervisor \
    nginx \
    libsodium-dev \
    && curl -fsSL https://deb.nodesource.com/setup_22.x | bash - \
    && apt-get install -y nodejs \
    && curl -sSLf -o /usr/local/bin/install-php-extensions https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions \
    && chmod +x /usr/local/bin/install-php-extensions \
    && install-php-extensions \
    apcu \
    bz2 \
    pcntl \
    mbstring \
    bcmath \
    sockets \
    pdo_pgsql \
    opcache \
    exif \
    pdo_mysql \
    zip \
    uv \
    intl \
    gd \
    redis \
    rdkafka \
    ffi \
    ldap \
    xdebug \
    && apt-get -y autoremove \
    && apt-get clean \
    && docker-php-source delete \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /var/log/lastlog /var/log/faillog

RUN arch="$(uname -m)" \
    && case "$arch" in \
    armhf) _cronic_fname='supercronic-linux-arm' ;; \
    aarch64) _cronic_fname='supercronic-linux-arm64' ;; \
    x86_64) _cronic_fname='supercronic-linux-amd64' ;; \
    x86) _cronic_fname='supercronic-linux-386' ;; \
    *) echo >&2 "error: unsupported architecture: $arch"; exit 1 ;; \
    esac \
    && curl -fsSL "https://github.com/aptible/supercronic/releases/download/v0.2.38/${_cronic_fname}" -o /usr/bin/supercronic \
    && chmod +x /usr/bin/supercronic \
    && mkdir -p /etc/supercronic \
    && echo "*/1 * * * * php ${ROOT}/artisan schedule:run --no-interaction" > /etc/supercronic/laravel

RUN groupadd --force -g ${GROUP_ID} ${USER} \
    && useradd -ms /bin/bash --no-log-init --no-user-group -g ${GROUP_ID} -u ${USER_ID} ${USER} \
    && userdel --remove --force www-data 2>/dev/null || true

RUN cp ${PHP_INI_DIR}/php.ini-production ${PHP_INI_DIR}/php.ini

COPY --link --from=vendor /usr/bin/composer /usr/bin/composer
COPY --link docker/php-fpm/supervisord.conf /etc/
COPY --link docker/php-fpm/supervisord.*.conf /etc/supervisor/conf.d/
COPY --link docker/php-fpm/start-container /usr/local/bin/start-container
COPY --link docker/php-fpm/healthcheck /usr/local/bin/healthcheck
COPY --link docker/php-fpm/php.ini ${PHP_INI_DIR}/conf.d/99-php.ini
COPY --link docker/php-fpm/php-fpm.conf /usr/local/etc/php-fpm.d/www.conf
COPY --link docker/php-fpm/nginx.conf /etc/nginx/nginx.conf
COPY --link composer.* ./

RUN composer install \
    --no-dev \
    --no-interaction \
    --no-autoloader \
    --no-ansi \
    --no-scripts \
    --no-progress \
    --audit

COPY --link package.json package-lock.json ./

RUN npm ci

COPY --link --chown=${USER_ID}:${GROUP_ID} . .

RUN mkdir -p \
    storage/framework/{sessions,views,cache,testing} \
    storage/logs \
    bootstrap/cache \
    /var/lib/nginx /var/cache/nginx /var/log/nginx /run/nginx \
    && chown -R ${USER_ID}:${GROUP_ID} ${ROOT}/storage ${ROOT}/bootstrap/cache \
    /var/lib/nginx /var/cache/nginx /var/log/nginx /run/nginx \
    && chmod +x /usr/local/bin/start-container /usr/local/bin/healthcheck

RUN composer dump-autoload \
    --optimize \
    --no-dev \
    --apcu

RUN npm run build

USER ${USER}

EXPOSE 80
EXPOSE 9000

ENTRYPOINT ["start-container"]

HEALTHCHECK --start-period=5s --interval=1s --timeout=3s --retries=10 CMD healthcheck || exit 1
