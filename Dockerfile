FROM php:5.6-apache

RUN apt-get update && apt-get install -y apt-utils \
  curl zlib1g-dev libxml2-dev libgd-dev libzip-dev libpng-dev

RUN docker-php-source extract

RUN docker-php-ext-install zlib; exit 0

RUN cp /usr/src/php/ext/zlib/config0.m4 /usr/src/php/ext/zlib/config.m4 && \
  ln -s /usr/include/x86_64-linux-gnu/curl /usr/local/include/curl && \
  apt-get install -y libcurl4-gnutls-dev

RUN docker-php-ext-install curl dom gd json mbstring pdo pdo_mysql session zip zlib && \
  mkdir -p /sacoche/__private /sacoche/__tmp && \
  chmod 777 /sacoche/__private && \
  chmod 777 /sacoche/__tmp && \
  docker-php-source delete
 
COPY src/ /var/www/html/
