FROM php:8.1.13-apache

# Installation de l'outil pour installer les extensions PHP
ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

# Copie de Composer depuis une autre image
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configuration des extensions PHP, installation de dépendances, etc.
RUN chmod +x /usr/local/bin/install-php-extensions && \
    install-php-extensions intl pdo_mysql && \
    apt update && apt install -yqq zip git

# Installation de Node.js et Yarn
RUN curl -fsSL https://deb.nodesource.com/setup_18.x | bash - && \
    apt-get update && \
    apt-get install -yqq nodejs && \
    npm install -g yarn

# Nettoyage des fichiers temporaires
RUN apt-get clean && \
    rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# Copie du fichier de configuration Apache
COPY ./.docker/prod/apache.conf /etc/apache2/sites-available/000-default.conf

# Copie du fichier de configuration php.ini
COPY ./.docker/prod/php.ini /usr/local/etc/php/conf.d/docker-php-config.ini

# Copie le entryPoint
COPY ./.docker/prod/entrypoint.sh /entrypoint.sh

# Copie du contenu du projet dans le répertoire /var/www/
COPY . /var/www/

# Définition du répertoire de travail
WORKDIR /var/www/

# Installation des dépendances avec Composer
RUN composer install --no-scripts

RUN chmod +w /var/www/public/uploads

# Ajout du bind mount vers le dossier public/images
VOLUME /var/www/public/uploads

RUN chmod +x /entrypoint.sh

ENTRYPOINT ["/entrypoint.sh"]