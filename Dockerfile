
FROM php:8.3-apache

RUN docker-php-ext-install mysqli

WORKDIR /var/www/html

COPY deverp-main/ /var/www/html/
COPY db.php /var/www/html/db.php

EXPOSE 80
