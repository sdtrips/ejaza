FROM php:8.2-apache

# تفعيل mod_rewrite لدعم .htaccess
RUN a2enmod rewrite

COPY . /var/www/html/
