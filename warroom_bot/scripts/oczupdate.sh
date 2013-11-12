#!/bin/bash

cd /var/www/html/spider

        echo '[[strat update_root overclockzone]]'
        php index.php fetch3 update_root 33
        echo 'sleep 30'
	sleep 30
	./scripts/page_collector.sh 30