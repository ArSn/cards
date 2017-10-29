FROM php:7.0-cli

COPY . /app
WORKDIR /app

CMD ["php", "./bin/cards-server.php"]

EXPOSE 80