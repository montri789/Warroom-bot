#!/bin/bash

cd /var/www/html/spider
while :
do
	php index.php parser run $1 $2 $3
	sleep 60
done