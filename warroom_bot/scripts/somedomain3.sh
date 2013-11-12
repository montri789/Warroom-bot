#!/bin/bash

cd /var/www/html/spider
array=(37 207 208)
for i in "${array[@]}"
do
        echo '[[strat fetch all' $i']]'
        php index.php fetch3 all $i
        echo 'sleep 30'
	sleep 30
	./scripts/page_collector.sh 30
done
