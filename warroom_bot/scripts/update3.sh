#!/bin/bash

cd /var/www/html/spider
array=(46 47 48 50 51 52 53 55 56 57 58 59 60 61 62 63 64 65 66 67 68)
for i in "${array[@]}"
do
        echo '[[strat update_root' $i']]'
        php index.php fetch3 update_root $i
        echo 'sleep 30'
	sleep 30
	./scripts/page_collector.sh 30
done