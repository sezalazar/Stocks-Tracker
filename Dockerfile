FROM php:8.3-fpm

# Install required dependencies and extensions
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libonig-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    curl \
    build-essential \
    && curl -fsSL https://deb.nodesource.com/setup_18.x | bash - \
    && apt-get install -y nodejs \
    # Install PHP extensions. Removed pdo_mysql, added pdo_pgsql:
    && docker-php-ext-install pdo_pgsql mbstring zip exif pcntl \
    # Install and enable Xdebug for debugging
    && pecl install xdebug \
    && docker-php-ext-enable xdebug \
    # Install Redis Extension
    && pecl install redis \
    && docker-php-ext-enable redis

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

RUN echo "memory_limit=512M" > /usr/local/etc/php/conf.d/memory-limit.ini

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .

COPY entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Set permission for laravel application
RUN chown -R www-data:www-data storage bootstrap/cache
RUN chmod -R 775 storage bootstrap/cache

EXPOSE 9000

# ENTRYPOINT ["entrypoint.sh"]
