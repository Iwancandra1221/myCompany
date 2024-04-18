FROM php:7.2-apache-buster

ARG NEW_RELIC_AGENT_VERSION
ARG NEW_RELIC_LICENSE_KEY
ARG NEW_RELIC_APPNAME

# COPY apache.conf /etc/apache2/apache2.conf
RUN mkdir /var/log/php/ && touch /var/log/php/php.log
WORKDIR /var/www/html

COPY . .

# debian 8 (jessie)
# RUN echo "deb [check-valid-until=no] http://archive.debian.org/debian jessie main" > /etc/apt/sources.list
# RUN sed -i '/deb-src http:\/\/archive.debian.org\/debian\/ jessie main/d' /etc/apt/sources.list
# RUN sed -i '/deb http:\/\/archive.debian.org\/debian-security jessie\/updates main/d' /etc/apt/sources.list
# RUN sed -i '/deb-src http:\/\/archive.debian.org\/debian\/ jessie main/d' /etc/apt/sources.list

# debian 9 (stretch)
# RUN echo "deb [check-valid-until=no] http://archive.debian.org/debian stretch main" > /etc/apt/sources.list
# RUN sed -i '/deb-src http:\/\/archive.debian.org\/debian\/ stretch main/d' /etc/apt/sources.list
# RUN sed -i '/deb http:\/\/archive.debian.org\/debian-security stretch\/updates main/d' /etc/apt/sources.list
# RUN sed -i '/deb-src http:\/\/archive.debian.org\/debian\/ stretch main/d' /etc/apt/sources.list

# RUN apt-get -o Acquire::Check-Valid-Until=false -qq -y update
# RUN apt-get --no-install-recommends -qq -y install apt-utils apt-transport-https

RUN apt-get update && apt-get --no-install-recommends -qq -y install apt-transport-https
RUN apt-get --no-install-recommends -qq -y install gnupg2 libzip-dev zlib1g-dev libfreetype6-dev libjpeg62-turbo-dev libpng-dev libmcrypt-dev
RUN apt-get --no-install-recommends -qq -y install sudo curl wget unzip nano rename build-essential libgss3 gcc openssl

RUN pecl channel-update pecl.php.net

# php < 7.3.0
RUN pecl install mcrypt-1.0.3 && docker-php-ext-enable mcrypt;

# RUN pecl install xdebug-2.9.0 && docker-php-ext-enable xdebug \
#     && echo "zend_extension=$(find /usr/local/lib/php/extensions/ -name xdebug.so)" > /usr/local/etc/php/conf.d/xdebug.ini \
#     && echo "xdebug.remote_enable=on" >> /usr/local/etc/php/conf.d/xdebug.ini \
#     && echo "xdebug.remote_autostart=off" >> /usr/local/etc/php/conf.d/xdebug.ini

RUN curl https://packages.microsoft.com/keys/microsoft.asc | sudo apt-key add - \
  && curl https://packages.microsoft.com/config/debian/10/prod.list > /etc/apt/sources.list.d/mssql-release.list \
  # debian 8 (jessie) 9 (stretch)
  && apt-get -o Acquire::Check-Valid-Until=false -qq -y update \
  # && sudo apt-get update \
  && sudo ACCEPT_EULA=Y apt-get --no-install-recommends -qq -y install msodbcsql17 mssql-tools unixodbc-dev=2.3.7 unixodbc=2.3.7 odbcinst1debian2=2.3.7 odbcinst=2.3.7 \
  # php >= 7.3.0
  # && sudo pecl install sqlsrv-5.9.0 \
  # && sudo pecl install pdo_sqlsrv-5.9.0 
  # php < 7.3.0
  && sudo pecl install sqlsrv-5.6.0 \
  # && docker-php-ext-enable sqlsrv
  && sudo pecl install pdo_sqlsrv-5.6.0
  # && docker-php-ext-enable pdo_sqlsrv

RUN docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ && docker-php-ext-install gd zip exif

# Add php.ini
COPY php.ini /usr/local/etc/php/php.ini
# sed -i -e "s/^ *memory_limit.*/memory_limit = 4G/g" /usr/local/etc/php/php.ini

# COPY browscap.ini /usr/local/etc/php/conf.d/browscap.ini

# RUN echo "zend_extension=/usr/local/lib/php/extensions/no-debug-non-zts-20180731/xdebug.so" >> /usr/local/etc/php/php.ini \
#     && echo "extension=pdo_sqlsrv.so" >>  /usr/local/etc/php/php.ini \
#     && echo "extension=sqlsrv.so" >> /usr/local/etc/php/php.ini \
#     && echo "extension=xdebug.so" >> /usr/local/etc/php/php.ini \
    # php < 7.2.0
    # && echo "extension=mcrypt.so" >> /usr/local/etc/php/php.ini

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN rm -rf application/controllers/composer.lock
RUN rm -rf application/controllers/vendor

RUN composer dump-autoload -o
RUN composer install --working-dir application/controllers --prefer-dist --ignore-platform-reqs --no-dev

RUN find application/models -depth | xargs -n 1 rename -v 's/(.*)\/([^\/]*)/$1\/\L$2/' {} \;
RUN find application/helpers -depth | xargs -n 1 rename -v 's/(.*)\/([^\/]*)/$1\/\L$2/' {} \;

# Add newrelic.ini
# COPY newrelic.ini /usr/local/etc/php/conf.d/newrelic.ini

RUN sed -i 's,^\(MinProtocol[ ]*=\).*,\1'TLSv1.0',g' /etc/ssl/openssl.cnf \
    && sed -i 's,^\(CipherString[ ]*=\).*,\1'DEFAULT@SECLEVEL=1',g' /etc/ssl/openssl.cnf

RUN a2enmod rewrite

RUN service apache2 restart

# RUN curl -L https://download.newrelic.com/php_agent/archive/${NEW_RELIC_AGENT_VERSION}/newrelic-php5-${NEW_RELIC_AGENT_VERSION}-linux.tar.gz | tar -C /tmp -zx \
#     && export NR_INSTALL_USE_CP_NOT_LN=1 \
#     && export NR_INSTALL_SILENT=1 \
#     && /tmp/newrelic-php5-${NEW_RELIC_AGENT_VERSION}-linux/newrelic-install install \
#     && rm -rf /tmp/newrelic-php5-* /tmp/nrinstall*

# RUN sed -i -e "s/REPLACE_WITH_REAL_KEY/${NEW_RELIC_LICENSE_KEY}/" \
#     -e "s/newrelic.appname[[:space:]]=[[:space:]].*/newrelic.appname=\"${NEW_RELIC_APPNAME}\"/" \
#     -e '$anewrelic.daemon.address="newrelic-php-daemon:31339"' \
#     $(php -r "echo(PHP_CONFIG_FILE_SCAN_DIR);")/newrelic.ini

# RUN curl -Ls https://download.newrelic.com/install/newrelic-cli/scripts/install.sh | bash && sudo NEW_RELIC_API_KEY=NRAK-KC3TRA2BQH2N0MPSWXJTPCP28O1 NEW_RELIC_ACCOUNT_ID=3807522 /usr/local/bin/newrelic install -n logs-integration

ENTRYPOINT ["docker-php-entrypoint"]
# https://httpd.apache.org/docs/2.4/stopping.html#gracefulstop
STOPSIGNAL SIGWINCH

EXPOSE 80
EXPOSE 443

CMD ["apache2-foreground"]
