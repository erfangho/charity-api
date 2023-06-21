FROM php:8.2 as php

RUN apt-get update -y
RUN apt-get install -y unzip libpq-dev libcurl4-gnutls-dev
RUN docker-php-ext-install pdo pdo_mysql bcmath

WORKDIR /var/www
COPY . .

COPY --from=composer:2.3.10 /usr/bin/composer /usr/bin/composer

ENV PORT=8000

CMD ["chmod +x Docker/entrypoint.sh"]

ENTRYPOINT [ "Docker/entrypoint.sh" ]
