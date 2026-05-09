FROM php:8.3-cli

RUN apt-get update && apt-get install -y \
    git curl zip unzip libsqlite3-dev \
  && docker-php-ext-install pdo pdo_sqlite bcmath \
  && apt-get clean && rm -rf /var/lib/apt/lists/*

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY composer.json composer.lock ./
RUN composer install --no-scripts --no-autoloader --prefer-dist

COPY . .

RUN composer dump-autoload --optimize

EXPOSE 8000