FROM nginx

COPY cards.kaz.cool.conf /etc/nginx/conf.d/
COPY . /app/web

RUN chmod -R ugo=rwx /app/web

RUN cd /app/web && rm cards.kaz.cool.conf && rm Dockerfile

EXPOSE 80