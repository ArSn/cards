FROM php:7.0-cli

VOLUME /app
COPY . /app
WORKDIR /app

CMD ["php", "./bin/cards-server.php"]