# Usa una imagen base de PHP con Apache
FROM php:8.4-apache

# Instala las dependencias necesarias para Laravel y PostgreSQL
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    zip \
    git \
    unzip \
    libpq-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_mysql pdo_pgsql

# Habilita el módulo de reescritura de Apache
RUN a2enmod rewrite

# Cambiar el DocumentRoot de Apache para apuntar al directorio public de Laravel
RUN sed -i 's|/var/www/html|/var/www/html/public|' /etc/apache2/sites-available/000-default.conf

# Establece el nombre de servidor para evitar el mensaje de advertencia
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Copia los archivos del proyecto al contenedor
COPY . /var/www/html

# Establece el directorio de trabajo dentro del contenedor
WORKDIR /var/www/html

# Instala Composer (gestor de dependencias PHP)
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Ejecuta Composer para instalar las dependencias de Laravel
RUN composer install --no-interaction --optimize-autoloader

# Da permisos de escritura en las carpetas necesarias de Laravel
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Expone el puerto en el que el servidor Apache estará escuchando
EXPOSE 80

# Comando para ejecutar el servidor de Apache
CMD ["apache2-foreground"]