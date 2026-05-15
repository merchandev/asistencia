FROM php:8.2-fpm-alpine

# Instalar extensiones PHP necesarias (Redis nativo vía PECL + PDO MySQL)
RUN apk add --no-cache pcre-dev $PHPIZE_DEPS \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && docker-php-ext-install pdo_mysql \
    && apk del pcre-dev $PHPIZE_DEPS

WORKDIR /var/www/html

# Copiar el código de la aplicación
COPY . /var/www/html

# Establecer permisos
RUN chown -R www-data:www-data /var/www/html

EXPOSE 9000
CMD ["php-fpm"]
