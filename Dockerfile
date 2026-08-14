# syntax=docker/dockerfile:1

# ============================================================
# ETAPA 1: Runtime PHP base
# ============================================================
FROM php:8.3-fpm-bookworm AS php-base

WORKDIR /var/www/html

RUN apt-get update \
  && apt-get install -y --no-install-recommends \
  libfreetype6-dev \
  libjpeg62-turbo-dev \
  libpng-dev \
  libicu-dev \
  libonig-dev \
  libzip-dev \
  unzip \
  git \
  curl \
  && docker-php-ext-configure gd \
  --with-freetype \
  --with-jpeg \
  && docker-php-ext-install -j$(nproc) \
  bcmath \
  gd \
  intl \
  mbstring \
  opcache \
  pdo_mysql \
  zip \
  && apt-get clean \
  && rm -rf /var/lib/apt/lists/*


# ============================================================
# ETAPA 2: Dependencias PHP + aplicación Laravel
# ============================================================
FROM php-base AS app-build

# Composer proviene de su imagen oficial.
# No lo descargamos mediante scripts arbitrarios.
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Primero copiamos únicamente los manifiestos de Composer.
COPY composer.json composer.lock ./

# Instalamos dependencias sin ejecutar scripts de Laravel todavía,
# porque el código fuente aún no ha sido copiado.
RUN composer install \
  --no-dev \
  --prefer-dist \
  --no-interaction \
  --no-progress \
  --no-scripts \
  --no-autoloader

# Ahora copiamos la aplicación.
COPY . .

# Generamos el autoload optimizado y ejecutamos los scripts
# normales definidos por Composer/Laravel.
RUN composer install \
  --no-dev \
  --prefer-dist \
  --no-interaction \
  --no-progress \
  --optimize-autoloader

# Verificación adicional de requisitos PHP.
RUN composer check-platform-reqs

CMD ["php-fpm"]