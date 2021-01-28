FROM php:7.3-fpm
    && apt-get install -y composer libicu-dev cron git
RUN docker-php-ext-install intl
WORKDIR /var/www
RUN composer install
COPY crontab.txt /crontab.txt
COPY script.sh /script.sh
COPY entry.sh /entry.sh
RUN chmod 755 /script.sh /entry.sh
RUN /usr/bin/crontab /crontab.txt
CMD /entry.sh ; php-fpm
EXPOSE 9000
