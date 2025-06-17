FROM ronaregen/php:frankenphp-latest AS main

WORKDIR /app

COPY . /app
COPY entrypoint.sh /usr/local/bin/entrypoint.sh

RUN composer install

RUN chmod +x /usr/local/bin/entrypoint.sh
RUN /usr/local/bin/entrypoint.sh

ENTRYPOINT ["php", "artisan", "octane:frankenphp"]

