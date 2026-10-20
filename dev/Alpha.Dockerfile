ARG PHP_VERSION=8.4
FROM php:${PHP_VERSION}-apache
RUN apt-get update && apt-get install -y --no-install-recommends unzip libfreetype6-dev libjpeg62-turbo-dev libpng-dev libonig-dev libzip-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j4 mysqli gd mbstring exif zip \
    && rm -rf /var/lib/apt/lists/* && a2enmod rewrite
ARG PIWIGO_VERSION=16.4.0
COPY .dev/core/ /tmp/core/
RUN unzip -q /tmp/core/piwigo-${PIWIGO_VERSION}.zip -d /tmp/piwigo \
    && cp -a /tmp/piwigo/piwigo/. /var/www/html/ \
    && rm -rf /tmp/piwigo /tmp/core && chown -R www-data:www-data /var/www/html
COPY dev/php.ini /usr/local/etc/php/conf.d/gjallarblog-dev.ini