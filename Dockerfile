#Use an official PHP runtime as a parent image
FROM php:8.2-apache

ENV COMPOSER_ALLOW_SUPERUSER=1
# Install dependencies
RUN apt-get update && apt-get install -y \
   build-essential \
   libpng-dev \
   libjpeg62-turbo-dev \
   libfreetype6-dev \
   locales \
   zip \
   jpegoptim optipng pngquant gifsicle \
   vim \
   unzip \
   git \
   curl \
   libonig-dev \
   libzip-dev \
   libpq-dev \
   librdkafka-dev \
   postgresql-client \
   && docker-php-ext-install pdo pdo_pgsql mbstring exif pcntl bcmath gd zip


# Install required system dependencies for rdkafka and sockets
RUN apt-get update && apt-get install -y librdkafka-dev libssl-dev

# Install PHP extensions
RUN pecl install rdkafka && docker-php-ext-enable rdkafka
RUN docker-php-ext-install sockets
# Enable Apache mod_rewrite
RUN a2enmod rewrite


# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*


# Set working directory
WORKDIR /var/www/html


# Copy existing application directory contents
COPY . /var/www/html

## Install necessary tools including netcat
#RUN apt-get update && apt-get install -y \
#    netcat \
#    && apt-get clean


# Copy existing application directory permissions
COPY --chown=www-data:www-data . /var/www/html

# Copy the entrypoint script
COPY entrypoint.sh /usr/local/bin/entrypoint.sh


RUN chmod +x /usr/local/bin/entrypoint.sh


# Install necessary tools including netcat
RUN apt-get update && apt-get install -y \
    netcat-openbsd \
    && apt-get clean

RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache




#copy apache conf
#COPY vhost.conf /etc/apache2/sites-available/000-default.conf
COPY vhost.conf /etc/apache2/sites-available/vhost.conf
RUN a2ensite vhost.conf
RUN a2dissite 000-default.conf


# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer


# Install Laravel dependencies
RUN composer install


# Ensure that the /var/www/html directory is writable by the web server
RUN chown -R www-data:www-data /var/www/html \
   && chmod -R 775 /var/www/html/storage \
   && chmod -R 775 /var/www/html/bootstrap/cache




# Change current user to www
#USER www-data


# Expose port 80
EXPOSE 80


# Start Apache in the foreground
CMD ["apache2-foreground"]
