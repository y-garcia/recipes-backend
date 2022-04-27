FROM php:7.2.34-alpine
RUN docker-php-ext-install pdo pdo_mysql
COPY --from=composer /usr/bin/composer /usr/local/bin/composer