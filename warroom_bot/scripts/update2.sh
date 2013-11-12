#!/bin/bash

cd /var/www/html/spider
array=(10 12 13 15 16 18 19 21 25 26 27 29 31 32 36 39 40 41 42 43 44)
for i in "${array[@]}"
do
        echo '[[strat update_root' $i']]'
        php index.php fetch3 update_root $i
        echo 'sleep 30'
	sleep 30
	./scripts/page_collector.sh 30
done