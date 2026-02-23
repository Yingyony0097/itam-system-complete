FROM php:8.2-apache

RUN set -eux; \
    apt-get update; \
    apt-get install -y --no-install-recommends \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
        libonig-dev \
    ; \
    docker-php-ext-configure gd --with-freetype --with-jpeg; \
    docker-php-ext-install -j"$(nproc)" pdo pdo_mysql mysqli mbstring gd; \
    rm -rf /var/lib/apt/lists/*

# Enable required Apache modules for clean URLs and legacy .htaccess directives
RUN a2enmod rewrite access_compat

# Allow .htaccess overrides (needed for mod_rewrite clean URLs)
RUN printf '%s\n' \
    '<Directory /var/www/html>' \
    '    AllowOverride All' \
    '    Require all granted' \
    '</Directory>' \
    > /etc/apache2/conf-available/zz-itam.conf \
 && a2enconf zz-itam

WORKDIR /var/www/html
