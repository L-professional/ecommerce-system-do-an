FROM php:8.2-apache

# Cài đặt extension kết nối MySQL cho PHP
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Hardening 1: Ẩn thông tin phiên bản PHP
RUN echo "expose_php = Off" > /usr/local/etc/php/conf.d/security.ini

# Hardening 2: Cấu hình Apache chạy port 8080 và chuyển quyền sang non-root user (www-data)
RUN sed -i 's/80/8080/g' /etc/apache2/ports.conf /etc/apache2/sites-available/*.conf \
    && mkdir -p /var/run/apache2 /var/lock/apache2 /var/log/apache2 \
    && chown -R www-data:www-data /var/run/apache2 /var/lock/apache2 /var/log/apache2 /var/www/html

USER www-data
EXPOSE 8080
