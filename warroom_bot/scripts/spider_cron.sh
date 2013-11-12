#!/bin/bash

cd /var/www/html/spider
php index.php fetch2 update_$2 $1
sleep 30
php index.php collector page 30
./keep-trying.sh php index.php fetch2 all $1
