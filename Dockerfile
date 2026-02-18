
FROM php:8.4-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    fcgi \
    git \
    curl \
    libpng-dev \
    libzip-dev \
    libjpeg-turbo-dev \
    oniguruma-dev \
    freetype-dev \
    icu-dev \
    $PHPIZE_DEPS && \
    docker-php-ext-install -j$(nproc) gd pdo_mysql zip bcmath exif pcntl opcache && \
    docker-php-ext-configure gd --with-freetype --with-jpeg && \
    docker-php-ext-install -j$(nproc) gd && \
    apk del $PHPIZE_DEPS

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install php-fpm-healthcheck
ADD https://raw.githubusercontent.com/renatomefi/php-fpm-healthcheck/master/php-fpm-healthcheck /usr/local/bin/php-fpm-healthcheck
RUN chmod +x /usr/local/bin/php-fpm-healthcheck

# Enable php-fpm status page for healthcheck
RUN sed -i 's/;pm.status_path = \/status/pm.status_path = \/status/' /usr/local/etc/php-fpm.d/www.conf

# Set working directory
WORKDIR /var/www/html

# Copy application code
COPY . /var/www/html

# Copy and prepare the entrypoint script
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]

# Expose port 9000 and start php-fpm server as the default command
EXPOSE 9000
CMD ["php-fpm"]
