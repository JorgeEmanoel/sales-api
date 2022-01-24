FROM jorgeemanoel/php:7.4-nginx

WORKDIR /var/www
COPY . /var/www

COPY ./docker/nginx.conf /etc/nginx/conf.d/default.conf

RUN chown -R www-data:www-data ./storage
RUN usermod -aG www-data root
RUN chmod -R g+w ./storage

CMD ["init"]
