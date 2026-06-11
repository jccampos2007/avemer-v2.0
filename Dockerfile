# Etapa 1: Node.js para las dependencias
FROM node:18 AS node-builder
WORKDIR /app
COPY package*.json ./
RUN npm install
COPY . .

# Etapa 2: PHP y Apache
FROM php:8.2-apache

# 1. Definir el DocumentRoot a la carpeta /public
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

# 2. Instalar dependencias del sistema y PHP
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \
    zip \
    unzip \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libwebp-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install pdo pdo_mysql pdo_pgsql zip gd

# 3. Configurar Apache para usar la nueva ruta
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# 4. Habilitar mod_rewrite para el sistema de rutas MVC
RUN a2enmod rewrite

# 5. Instalar Composer (necesario para el autoloader)
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 6. Copiar el código del proyecto
WORKDIR /var/www/html
COPY . .

# 7. Copiar node_modules desde la etapa anterior
COPY --from=node-builder /app/node_modules /var/www/html/node_modules

# 8. Instalar dependencias de PHP y optimizar
RUN composer install --no-interaction --optimize-autoloader

# Añade esto antes del comando chown en tu Dockerfile
RUN sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

# 9. Ajustar permisos finales
RUN chown -R www-data:www-data /var/www/html && chmod -R 755 /var/www/html