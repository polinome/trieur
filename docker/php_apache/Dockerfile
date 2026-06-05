# syntax=docker/dockerfile:1.7

FROM docker.io/composer:2 AS composer

FROM php:8.4-apache

ENV HOME=/var/home

# Install helper for PHP extensions
COPY --from=mlocati/php-extension-installer:latest /usr/bin/install-php-extensions /usr/local/bin/

# Install system dependencies + PHP extensions
RUN apt-get update && apt-get install -y --no-install-recommends \
        git \
        openssh-client \
        wget \
        zip \
        unzip \
    && install-php-extensions \
        intl \
        pdo_mysql \
        zip \
        bcmath \
        soap \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer /usr/bin/composer /usr/local/bin/composer

# Install Xdebug
RUN install-php-extensions xdebug
COPY conf/mods/xdebug.ini /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

# Home directory
RUN mkdir -p /var/home \
    && chown -R www-data:www-data /var/home

# PHP configuration
COPY conf/php.ini /usr/local/etc/php/conf.d/custom.ini

# Apache vhost
COPY vhosts/app.conf /etc/apache2/sites-available/app.conf

RUN a2ensite app.conf \
    && a2dissite 000-default.conf

WORKDIR /var/www

# Example Composer cache usage (when you add composer install)
# COPY composer.json composer.lock ./
# RUN --mount=type=cache,target=/tmp/composer-cache \
#     composer install --no-dev --prefer-dist --no-interaction

USER www-data
