FROM php:8.2-fpm-alpine

# Instalar dependencias del sistema y de PHP necesarias para MySQL y Redis
RUN apk add --no-cache pcre-dev $PHPIZE_DEPS \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && docker-php-ext-install pdo_mysql \
    && apk del pcre-dev $PHPIZE_DEPS

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copiar el código de la aplicación
WORKDIR /var/www/html
COPY . /var/www/html

# Instalar dependencias de PHP usando Composer
RUN composer install --no-dev --optimize-autoloader

# Establecer el usuario y permisos
RUN chown -R www-data:www-data /var/www/html

# CMD por defecto es php-fpm (viene de la imagen base)
EXPOSE 9000
CMD ["php-fpm"]
