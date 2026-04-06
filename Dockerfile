FROM php:8.2-fpm-alpine

# System dependencies
RUN apk add --no-cache \
    git curl zip unzip \
    libpng-dev libxml2-dev libpq-dev \
    nginx supervisor nodejs npm

# PHP extensions
RUN docker-php-ext-install \
    pdo pdo_pgsql pgsql pdo_mysql \
    bcmath gd xml opcache

# Tune opcache for production
RUN { \
    echo 'opcache.enable=1'; \
    echo 'opcache.memory_consumption=256'; \
    echo 'opcache.interned_strings_buffer=16'; \
    echo 'opcache.max_accelerated_files=20000'; \
    echo 'opcache.validate_timestamps=0'; \
    echo 'opcache.revalidate_freq=0'; \
} > /usr/local/etc/php/conf.d/opcache.ini

# Allow large file uploads (camera photos can be 5-20 MB)
RUN { \
    echo 'upload_max_filesize=25M'; \
    echo 'post_max_size=30M'; \
    echo 'max_execution_time=60'; \
} > /usr/local/etc/php/conf.d/uploads.ini

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Install PHP dependencies first (layer cache)
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-scripts --no-interaction

# Install Node dependencies and build assets
COPY package*.json ./
RUN npm ci
COPY . .
RUN npm run build && rm -rf node_modules

# Finish Composer setup
RUN composer dump-autoload --optimize

# Permissions
RUN chown -R www-data:www-data storage bootstrap/cache && \
    chmod -R 775 storage bootstrap/cache

# Docker config files
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/supervisord.conf /etc/supervisord.conf
COPY docker/start.sh /start.sh
RUN chmod +x /start.sh

EXPOSE 80

CMD ["/start.sh"]
