# Этап 1: этап сборки
FROM php:8.2-fpm-alpine as build

# Установка системных зависимостей и php расширений
RUN apk add --no-cache \
    libpq-dev \
    zip \
    libzip-dev \
    freetype \
    libjpeg-turbo \
    libpng \
    freetype-dev \
    libjpeg-turbo-dev \
    libpng-dev \
    nodejs \
    npm \
    && docker-php-ext-configure zip \
    && docker-php-ext-install zip pdo pdo_mysql pgsql pdo_pgsql \
    && docker-php-ext-configure gd --with-freetype=/usr/include/ --with-jpeg=/usr/include/ \
    && docker-php-ext-install -j$(nproc) gd \
    && docker-php-ext-enable gd

# Установка composer
COPY --from=composer:2.7.6 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Копирование необходимых файлов и изменение разрешений
COPY . .
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/storage \
    && chmod -R 775 /var/www/html/bootstrap/cache

# установка php и node.js зависимостей
RUN composer install --no-dev --prefer-dist \
    && npm install \
    && npm run build

RUN chown -R www-data:www-data /var/www/html/vendor \
    && chmod -R 775 /var/www/html/vendor

# Этап 2: этап продакшена
FROM php:8.2-fpm-alpine

# Установка nginx
RUN apk add --no-cache \
    libpq-dev \
    zip \
    libzip-dev \
    freetype \
    libjpeg-turbo \
    libpng \
    freetype-dev \
    libjpeg-turbo-dev \
    libpng-dev \
    oniguruma-dev \
    gettext-dev \
    freetype-dev \
    nginx \
    supervisor \
    && docker-php-ext-configure zip \
    && docker-php-ext-install zip pdo pdo_mysql pgsql pdo_pgsql \
    && docker-php-ext-configure gd --with-freetype=/usr/include/ --with-jpeg=/usr/include/ \
    && docker-php-ext-install -j$(nproc) gd \
    && docker-php-ext-enable gd \
    && docker-php-ext-install bcmath \
    && docker-php-ext-enable bcmath \
    && docker-php-ext-install exif \
    && docker-php-ext-enable exif \
    && docker-php-ext-install gettext \
    && docker-php-ext-enable gettext \
    && docker-php-ext-install opcache \
    && docker-php-ext-enable opcache \
    && rm -rf /var/cache/apk/*

# Копирование файлов с этапа сборки
COPY --from=build /var/www/html /var/www/html
COPY ./deploy/nginx.conf /etc/nginx/http.d/default.conf
COPY ./deploy/php.ini "$PHP_INI_DIR/conf.d/app.ini"
COPY ./deploy/supervisord.ini /etc/supervisor.d/supervisord.ini

WORKDIR /var/www/html

VOLUME ["/var/www/html/storage/app"]

#CMD ["sh", "-c", "nginx && php-fpm"]
COPY ./deploy/app_deploy.sh /var/www/app_deploy.sh
RUN ["chmod", "+x", "/var/www/app_deploy.sh"]
#RUN adduser -D vivek
#RUN chown -R vivek:vivek /var/www/html
#RUN chown -R vivek:vivek /var/lib/nginx
#USER vivek
CMD ["sh", "/var/www/app_deploy.sh"]
