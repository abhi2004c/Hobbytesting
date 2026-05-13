FROM php:8.3-cli-bookworm

# System deps
RUN apt-get update && apt-get install -y --no-install-recommends \
    git curl unzip libpng-dev libonig-dev libxml2-dev libzip-dev \
    libgd-dev libexif-dev libicu-dev \
    && rm -rf /var/lib/apt/lists/*

# PHP extensions
RUN docker-php-ext-install \
    pdo pdo_pgsql mbstring xml zip gd exif intl bcmath pcntl tokenizer fileinfo

# Redis extension (phpredis)
RUN pecl install redis && docker-php-ext-enable redis

# Node.js 22
RUN curl -fsSL https://deb.nodesource.com/setup_22.x | bash - \
    && apt-get install -y nodejs \
    && rm -rf /var/lib/apt/lists/*

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Install PHP deps first (layer cache)
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

# Install Node deps and build assets
COPY package.json package-lock.json .npmrc ./
RUN npm ci --ignore-scripts

COPY . .
RUN npm run build

# Laravel setup
RUN php artisan storage:link --force || true

EXPOSE 8000

CMD php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=${PORT:-8000}
