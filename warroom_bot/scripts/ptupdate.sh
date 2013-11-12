#!/bin/bash

cd /var/www/html/spider
array=(181 182 183 184 185 186 187 188 189 190 191 192 193 194 195 196 197 198 199 200 201 202 203 204)
for i in "${array[@]}"
do
        echo '[[strat update_root pantip' $i']]'
        php index.php fetch3 update_root $i
        echo 'sleep 30'
	sleep 30
	./scripts/page_collector.sh 30
done