FROM php:7.1

RUN mkdir /app
ADD . /app


CMD ["php", "-S", "0.0.0.0:80", "-t", "/app/src"]
