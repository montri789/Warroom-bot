#!/bin/bash

cd /var/www/html/spider
array=(2 3 4 20 27 28 30 34 35)
for i in "${array[@]}"
do
        echo '[[strat update_root' $i']]'
        php index.php fetch3 update_root $i
        echo 'sleep 30'
	sleep 30
	./scripts/page_collector.sh 30
done