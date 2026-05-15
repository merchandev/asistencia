# ---- Etapa 1: Instalar dependencias con Composer ----
FROM composer:latest AS composer_deps

WORKDIR /app
COPY composer.json ./
RUN composer install \
    --no-dev \
    --optimize-autoloader \
    --no-interaction \
    --no-scripts \
    --prefer-dist \
    --ignore-platform-reqs

# ---- Etapa 2: Imagen final de producción ----
FROM php:8.2-fpm-alpine

# Instalar extensiones PHP necesarias
RUN apk add --no-cache pcre-dev $PHPIZE_DEPS \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && docker-php-ext-install pdo_mysql \
    && apk del pcre-dev $PHPIZE_DEPS

WORKDIR /var/www/html

# Copiar el código de la aplicación
COPY . /var/www/html

# Copiar vendor/ generado en la etapa anterior
COPY --from=composer_deps /app/vendor /var/www/html/vendor

# Establecer el usuario y permisos
RUN chown -R www-data:www-data /var/www/html

EXPOSE 9000
CMD ["php-fpm"]
