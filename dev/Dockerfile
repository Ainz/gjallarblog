FROM php:8.4-apache
RUN apt-get update && apt-get install -y --no-install-recommends unzip libfreetype6-dev libjpeg62-turbo-dev libpng-dev libonig-dev libzip-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" mysqli gd mbstring exif zip \
    && rm -rf /var/lib/apt/lists/* \
    && a2enmod rewrite
COPY .dev/core/piwigo-16.4.0.zip /tmp/piwigo.zip
RUN echo '72c58a337a1a0639b6ad8dc8f5900a2781fc19091b60ae157eb2ee58c5431c17  /tmp/piwigo.zip' | sha256sum -c - \
    && unzip -q /tmp/piwigo.zip -d /tmp/piwigo \
    && cp -a /tmp/piwigo/piwigo/. /var/www/html/ \
    && rm -rf /tmp/piwigo /tmp/piwigo.zip \
    && chown -R www-data:www-data /var/www/html
COPY dev/php.ini /usr/local/etc/php/conf.d/gjallarblog-dev.ini
