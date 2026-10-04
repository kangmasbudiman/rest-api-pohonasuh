#!/bin/sh
set -e

# Folder runtime Laravel harus bisa ditulis oleh php-fpm (www-data).
mkdir -p storage/framework/cache/data storage/framework/sessions \
         storage/framework/testing storage/framework/views storage/logs \
         storage/app/backups storage/app/firebase storage/app/public \
         public/assets public/upload bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache public/assets public/upload

exec "$@"
