ARG PHP_VERSION=8.5

FROM php:${PHP_VERSION}-fpm-bookworm

ARG APP_USER=www-data
ARG APP_UID=33
ARG APP_GID=33

WORKDIR /var/www/html

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        curl \
        git \
        unzip \
        zip \
        libpq-dev \
        libzip-dev \
        libicu-dev \
        libonig-dev \
        libxml2-dev \
        libcurl4-openssl-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
        libfreetype6-dev \
        libwebp-dev \
        libmagickwand-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j"$(nproc)" \
        bcmath \
        exif \
        gd \
        intl \
        pcntl \
        pdo_pgsql \
        pgsql \
        zip \
    && pecl install redis imagick \
    && docker-php-ext-enable redis imagick \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/pear \
    && mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

RUN { \
        echo 'memory_limit=512M'; \
        echo 'upload_max_filesize=32M'; \
        echo 'post_max_size=32M'; \
        echo 'max_execution_time=120'; \
        echo 'opcache.enable=1'; \
        echo 'opcache.enable_cli=1'; \
        echo 'opcache.validate_timestamps=1'; \
    } > "$PHP_INI_DIR/conf.d/99-app.ini"

RUN groupmod -o -g "$APP_GID" "$APP_USER" \
    && usermod -o -u "$APP_UID" -g "$APP_GID" "$APP_USER"

USER ${APP_USER}

CMD ["php-fpm"]
