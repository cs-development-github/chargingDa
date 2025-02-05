FROM php:8.1.13-apache

ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN chmod +x /usr/local/bin/install-php-extensions && \
    install-php-extensions intl pdo_mysql && \
    apt update && apt install -yqq zip git

# Install Node.js 18 and NPM
RUN curl -fsSL https://deb.nodesource.com/setup_18.x | bash -
RUN apt-get update && \
    apt-get install -yqq nodejs && \
    npm install -g yarn

RUN curl -sS https://get.symfony.com/cli/installer | bash
RUN mv /root/.symfony5/bin/symfony /usr/local/bin/symfony
RUN a2enmod rewrite

RUN apt-get update && apt-get install -y ca-certificates && update-ca-certificates

COPY  ./.docker/dev/apache.conf /etc/apache2/sites-available/000-default.conf
COPY ./.docker/dev/php.ini /usr/local/etc/php/conf.d/docker-php-config.ini

WORKDIR /var/www/