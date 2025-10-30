FROM php:8.2-apache

# Ajusta DocumentRoot via env
ARG APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e "s!/var/www/html!${APACHE_DOCUMENT_ROOT}!g" /etc/apache2/sites-available/000-default.conf \
    && sed -ri -e "s!/var/www/!${APACHE_DOCUMENT_ROOT}!g" /etc/apache2/apache2.conf \
    && a2enmod rewrite

# Dependências e extensões
RUN apt-get update && apt-get install -y git unzip zlib1g-dev libzip-dev \
    && docker-php-ext-install pdo pdo_mysql zip

# Xdebug
RUN pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && echo "xdebug.start_with_request=yes\nxdebug.client_port=9003\nxdebug.log_level=0" > /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

RUN docker-php-ext-install bcmath

# Copia nosso vhost customizado
COPY docker/apache/000-default.conf /etc/apache2/sites-available/000-default.conf

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
WORKDIR /var/www/html
