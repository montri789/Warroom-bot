#!/bin/bash

cd /var/www/html/spider
./keep-trying.sh php index.php fetch2 all $1
