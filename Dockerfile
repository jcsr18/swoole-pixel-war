FROM phpswoole/swoole:5.0.2-php8.2

COPY server/server.php /var/www

EXPOSE 9501

ENTRYPOINT ["php", "/var/www/index.php", "start"]