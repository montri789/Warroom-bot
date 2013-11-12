#!/bin/bash
$@
while [ $? -ne 0 ]
do
	echo "Retry"
	$@
done
