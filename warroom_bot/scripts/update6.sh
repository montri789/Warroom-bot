#!/bin/bash

cd /var/www/html/spider
array=(37 49 52 58 89 209 206 207 208)
for i in "${array[@]}"
do
        echo '[[strat update_root' $i']]'
        php index.php fetch3 update_root $i
        echo 'sleep 30'
	sleep 30
	./scripts/page_collector.sh 30
done
