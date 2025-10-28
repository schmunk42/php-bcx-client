FROM php:8.4-cli-alpine

# Install system dependencies
RUN apk add --no-cache \
    git \
    unzip \
    zip \
    libzip-dev \
    $PHPIZE_DEPS

# Install PHP extensions
RUN docker-php-ext-install \
    zip \
    opcache

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /repo

# Configure PHP for development
RUN echo "memory_limit = 256M" > /usr/local/etc/php/conf.d/memory.ini && \
    echo "opcache.enable_cli = 1" > /usr/local/etc/php/conf.d/opcache-cli.ini && \
    echo "opcache.jit = tracing" >> /usr/local/etc/php/conf.d/opcache-cli.ini && \
    echo "opcache.jit_buffer_size = 64M" >> /usr/local/etc/php/conf.d/opcache-cli.ini

# Copy composer files first for better caching
COPY composer.json composer.lock* ./

# Install dependencies
RUN composer install --no-scripts --no-autoloader --prefer-dist

# Copy application files
COPY . .

# Generate autoloader
RUN composer dump-autoload --optimize

CMD ["php", "-a"]
