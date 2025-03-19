FROM redis:latest

RUN apt-get update && apt-get install -y gettext-base && apt-get clean

RUN mkdir -p /etc/redis/

COPY default.conf /tmp/default.conf

ENTRYPOINT ["sh", "-c", "envsubst < /tmp/default.conf > /etc/redis/redis.conf"]
CMD ["redis-server", "/etc/redis/redis.conf"]
