#!/bin/bash

cd /var/www/html/spider
array=(74 76 81 82 83 85 86 87 89 93 95 98 99 100 102 103 106 107 108 109 110)
for i in "${array[@]}"
do
        echo '[[strat update_root' $i']]'
        php index.php fetch3 update_root $i
        echo 'sleep 30'
	sleep 30
	./scripts/page_collector.sh 30
done