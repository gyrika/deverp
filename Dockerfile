FROM php:8.3-apache

RUN docker-php-ext-install mysqli

WORKDIR /var/www/html

COPY index.php register.php logout.php dashboard.php \
    products.php add_product.php edit_product.php delete_product.php \
    new_order.php orders.php order.php style.css ./
COPY docker/db.php ./db.php

EXPOSE 80
