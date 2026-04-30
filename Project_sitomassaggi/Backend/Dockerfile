FROM php:apache-bookworm
WORKDIR /var/www/backend
RUN apt-get update && apt-get install -y \
    gnupg2 \
    curl \
    git \
    unzip \
    unixodbc-dev \
    lsb-release \
    libargon2-1 \
    libargon2-dev \
    nano


RUN a2enmod rewrite
RUN sed -i 's#DocumentRoot /var/www/html#DocumentRoot /var/www/backend/public#' /etc/apache2/sites-available/000-default.conf
RUN curl -sSL https://packages.microsoft.com/keys/microsoft.asc | gpg --dearmor > /usr/share/keyrings/microsoft.gpg
RUN echo "deb [arch=amd64 signed-by=/usr/share/keyrings/microsoft.gpg] https://packages.microsoft.com/debian/12/prod bookworm main" \
    > /etc/apt/sources.list.d/mssql-release.list
RUN apt-get update && ACCEPT_EULA=Y apt-get install -y msodbcsql18
RUN pecl install sqlsrv-5.13.0 pdo_sqlsrv-5.13.0 && docker-php-ext-enable sqlsrv pdo_sqlsrv

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer



COPY ./apache2.conf /etc/apache2/apache2.conf
COPY . /var/www/backend
RUN composer install --no-interaction --no-dev --optimize-autoloader
RUN chown -R www-data:www-data /var/www/backend
RUN chmod -R 755 /var/www/backend
