FROM php:7.3-fpm
RUN apt-get update \
    && apt-get install -y libicu-dev cron git
RUN docker-php-ext-install intl
COPY --from=composer /usr/bin/composer /usr/bin/composer
WORKDIR /var/www
CMD composer install
ADD crontab.txt /crontab.txt
ADD script.sh /script.sh
COPY entry.sh /entry.sh
RUN chmod 755 /script.sh /entry.sh
RUN /usr/bin/crontab /crontab.txt
CMD /entry.sh ; php-fpm
EXPOSE 9000