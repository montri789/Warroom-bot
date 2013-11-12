<?php

//echo "test .....";

$db_server = "localhost"; 
$db_user = "root"; 
$db_pwd = "usrobotic";
$db_db = "warroom"; 
	
if (!$cnn = mysql_connect($db_server, $db_user, $db_pwd)) { 
	echo mysql_error(); 
	exit(); 
} 
if (!mysql_select_db($db_db, $cnn)) { 
	echo mysql_error(); 
	exit(); 
} 

$sql = "select * from subject"; 
if (!$res = mysql_query($sql, $cnn)) { 
	echo mysql_error(); 
	exit(); 
} 

while ($row = mysql_fetch_array($res, MYSQL_ASSOC)) { 
	//$data[] = $row;
	//$data = $row;
	echo $row[subject]."\n";
} 	
?>