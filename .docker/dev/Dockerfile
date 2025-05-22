FROM php:8.1.13-apache

# 🔹 Extension installer + Composer
ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN chmod +x /usr/local/bin/install-php-extensions && \
    install-php-extensions intl pdo_mysql

# 🔹 Apache rewrite module
RUN a2enmod rewrite

# 🔹 PHP config
COPY ./.docker/dev/php.ini /usr/local/etc/php/conf.d/docker-php-config.ini

# 🔹 Apache vhost
COPY ./.docker/dev/apache.conf /etc/apache2/sites-available/000-default.conf

# 🔹 Symfony CLI
RUN curl -sS https://get.symfony.com/cli/installer | bash && \
    mv /root/.symfony*/bin/symfony /usr/local/bin/symfony

# 🔹 Node.js 18 + Yarn
RUN curl -fsSL https://deb.nodesource.com/setup_18.x | bash - && \
    apt-get update && \
    apt-get install -y nodejs && \
    npm install -g yarn

# 🔹 wkhtmltopdf (sans Qt) + ghostscript (via APT)
RUN apt-get update && apt-get install -y \
    wkhtmltopdf \
    ghostscript \
    xfonts-base \
    xfonts-75dpi \
    libxrender1 \
    libjpeg62-turbo \
    fontconfig

# 🔹 CA certs
RUN apt-get install -y ca-certificates && update-ca-certificates

# 🔹 Dossier PDF + droits
RUN mkdir -p /var/www/public/pdf && \
    chown -R www-data:www-data /var/www/public/pdf && \
    chmod -R 775 /var/www/public/pdf

# 🔹 Point de travail
WORKDIR /var/www