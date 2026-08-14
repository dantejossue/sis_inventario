# syntax=docker/dockerfile:1

# ============================================================
# ETAPA 1: Runtime PHP
# ============================================================
FROM php:8.3-fpm-bookworm AS php-runtime

WORKDIR /var/www/html

RUN set -eux; \
  apt-get update; \
  apt-get install -y --no-install-recommends \
  libfreetype6 \
  libjpeg62-turbo \
  libpng16-16 \
  libicu72 \
  libonig5 \
  libzip4 \
  libfreetype6-dev \
  libjpeg62-turbo-dev \
  libpng-dev \
  libicu-dev \
  libonig-dev \
  libzip-dev \
  ; \
  docker-php-ext-configure gd \
  --with-freetype \
  --with-jpeg; \
  docker-php-ext-install -j"$(nproc)" \
  bcmath \
  gd \
  intl \
  mbstring \
  opcache \
  pdo_mysql \
  zip; \
  apt-get purge -y --auto-remove \
  libfreetype6-dev \
  libjpeg62-turbo-dev \
  libpng-dev \
  libicu-dev \
  libonig-dev \
  libzip-dev; \
  rm -rf /var/lib/apt/lists/*


# ============================================================
# ETAPA 2: Composer + Laravel
# ============================================================
FROM php-runtime AS app-build

RUN apt-get update \
  && apt-get install -y --no-install-recommends \
  git \
  unzip \
  && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY composer.json composer.lock ./

RUN composer install \
  --no-dev \
  --prefer-dist \
  --no-interaction \
  --no-progress \
  --no-scripts \
  --no-autoloader

COPY . .

RUN composer install \
  --no-dev \
  --prefer-dist \
  --no-interaction \
  --no-progress \
  --optimize-autoloader

RUN composer check-platform-reqs


# ============================================================
# ETAPA 3: Frontend Vite
# ============================================================
FROM node:22-bookworm-slim AS frontend-build

WORKDIR /app

RUN npm install --global pnpm@10.27.0

COPY package.json pnpm-lock.yaml ./

RUN pnpm install --frozen-lockfile

COPY . .

RUN pnpm run build


# ============================================================
# ETAPA 4: Artefactos finales de la aplicación
# ============================================================
FROM app-build AS app-artifacts

COPY --from=frontend-build \
  /app/public/build \
  /var/www/html/public/build

# ============================================================
# ETAPA 5: Servidor web Nginx
# ============================================================
FROM nginx:alpine AS web

WORKDIR /var/www/html

COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf

COPY --from=app-artifacts \
  /var/www/html/public \
  /var/www/html/public

# ============================================================
# ETAPA 6: Runtime PHP final
# ============================================================
FROM php-runtime AS runtime

WORKDIR /var/www/html

COPY --from=app-artifacts \
  /var/www/html \
  /var/www/html

RUN mkdir -p \
  storage/framework/cache/data \
  storage/framework/sessions \
  storage/framework/views \
  storage/logs \
  bootstrap/cache \
  && chown -R www-data:www-data \
  storage \
  bootstrap/cache

CMD ["php-fpm"]