#!/bin/sh

php bin/console cache:warmup --env prod
php bin/console d:m:m
php bin/console asset:install

chmod 777 public/Users

apache2-foreground