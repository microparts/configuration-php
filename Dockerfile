FROM roquie/composer-parallel

WORKDIR /app
COPY . /app

RUN composer install \
    && vendor/bin/phpunit

CMD vendor/bin/phpunit
