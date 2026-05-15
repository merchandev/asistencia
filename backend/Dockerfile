FROM php:8.2-fpm-alpine

# Instalar dependencias del sistema necesarias para PHP, Composer y sus paquetes
RUN apk add --no-cache \
        git \
        zip \
        unzip \
        libzip-dev \
        pcre-dev \
        $PHPIZE_DEPS \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && docker-php-ext-install pdo_mysql zip \
    && apk del pcre-dev $PHPIZE_DEPS

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copiar el código de la aplicación
WORKDIR /var/www/html
COPY . /var/www/html

# Instalar dependencias de PHP con Composer
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

# Establecer el usuario y permisos
RUN chown -R www-data:www-data /var/www/html

# CMD por defecto es php-fpm (viene de la imagen base)
EXPOSE 9000
CMD ["php-fpm"]
