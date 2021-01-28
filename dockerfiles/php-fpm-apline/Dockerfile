FROM php:7.3-fpm-alpine
RUN apk add --update-cache \
        curl \
        icu-dev \
        php \
        php-mbstring \
        php-ctype \
        php-iconv \
        php-intl \
        php-sqlite3 \
        git
RUN docker-php-ext-install intl
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
RUN php composer-setup.php --install-dir=/usr/bin/ --filename=composer
WORKDIR /var/www
CMD composer install
COPY dockerfiles/crontab.txt /crontab.txt
COPY dockerfiles/script.sh /script.sh
COPY dockerfiles/php-fpm-apline/entry.sh /entry.sh
RUN chmod 755 /script.sh /entry.sh
RUN /usr/bin/crontab /crontab.txt
CMD ["/entry.sh"]
EXPOSE 9000
