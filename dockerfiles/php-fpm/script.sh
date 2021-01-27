#!/bin/sh
i=0
cd /var/www
while [ $i -lt 6 ]; do
  /usr/local/bin/php src/main.php &
  sleep 10
  i=$(( i + 1 ))
done