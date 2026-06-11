#!/bin/sh
set -eu

mkdir -p \
    storage/app/public/uploads/artikel/content \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs

php artisan storage:link --force

# Keep old article HTML that references /uploads/... working.
rm -rf public/uploads
ln -s ../storage/app/public/uploads public/uploads

php artisan optimize

exec php artisan serve --host=0.0.0.0 --port="${PORT:-8080}"
