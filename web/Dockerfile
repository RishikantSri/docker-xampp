# Base image: PHP 8.3 + Apache (Debian)
FROM php:8.3-apache

# Install OS packages + PHP extensions commonly needed for Laravel
RUN apt-get update && apt-get install -y \
    git unzip curl ca-certificates gnupg \
    libzip-dev libonig-dev libpng-dev libjpeg-dev libfreetype6-dev \
    libxml2-dev libicu-dev \
    supervisor \
  && docker-php-ext-configure gd --with-freetype --with-jpeg \
  && docker-php-ext-install -j$(nproc) \
    gd pdo_mysql mbstring exif pcntl bcmath xml intl zip opcache \
  && rm -rf /var/lib/apt/lists/*

# Optional: Xdebug (installed but NOT enabled by default in laravel.ini)
RUN pecl install xdebug \
  && docker-php-ext-enable xdebug

# Optional: MongoDB PHP extension (comment out if you don't need it)
RUN pecl install mongodb \
  && docker-php-ext-enable mongodb

# Enable Apache modules required by most Laravel apps
RUN a2enmod rewrite headers

# Install Composer (copy from official image)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Workdir used by bind mount (./projects -> /var/www/html)
WORKDIR /var/www/html

# Supervisor directories
RUN mkdir -p /etc/supervisor/conf.d /var/log/supervisor

EXPOSE 80 8000 8001
