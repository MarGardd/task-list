FROM redis:latest

RUN apt-get update && apt-get install -y gettext-base && apt-get clean

RUN mkdir -p /etc/redis/

COPY default.conf /tmp/default.conf

CMD ["sh", "-c", "envsubst < /tmp/default.conf > /etc/redis/redis.conf && redis-server /etc/redis/redis.conf"]
