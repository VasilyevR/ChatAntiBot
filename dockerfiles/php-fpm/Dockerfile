FROM php:7.3-fpm
RUN apt-get update \
    && apt-get install -y libicu-dev cron git
RUN docker-php-ext-install intl
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
RUN php composer-setup.php --install-dir=/usr/local/bin/ --filename=composer
WORKDIR /var/www
RUN composer install
COPY crontab.txt /crontab.txt
COPY script.sh /script.sh
COPY entry.sh /entry.sh
RUN chmod 755 /script.sh /entry.sh
RUN /usr/bin/crontab /crontab.txt
CMD /entry.sh ; php-fpm
EXPOSE 9000
