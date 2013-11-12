#!/bin/bash

cd /var/www/html/spider
while :
do
	php index.php fetch update_$2 $1
	sleep 30
	php index.php collector page 30
	php index.php fetch all $1
	php index.php parser run $1
	echo 'sleep 12 hours'
	sleep 12000
done
