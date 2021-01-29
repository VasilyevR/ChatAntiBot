FROM php:7.3-fpm
RUN apt-get update \
    && apt-get install -y libicu-dev libzip-dev zip cron git
RUN docker-php-ext-configure zip --with-libzip
RUN docker-php-ext-install zip intl
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
RUN php composer-setup.php --install-dir=/usr/bin/ --filename=composer
WORKDIR /var/www
COPY . /var/www
RUN touch var/script.log
RUN composer install
COPY dockerfiles/crontab.txt /crontab.txt
COPY dockerfiles/script.sh /script.sh
COPY dockerfiles/php-fpm/entry.sh /entry.sh
RUN chmod 755 /script.sh /entry.sh
RUN /usr/bin/crontab /crontab.txt
CMD /entry.sh ; php-fpm
EXPOSE 9000
