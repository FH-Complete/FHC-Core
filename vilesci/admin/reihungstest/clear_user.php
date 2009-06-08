<?php
	include('config.inc.php');
	if(!($dbh = @mysql_connect($dbhost, $dbuser, $dbpasswd))) 
	{
        die("Error: Cannot connect to database $dbhost");
	}
	mysql_select_db($dbname); 
	$query  = "delete from rt_antwort where 1";
	$result = mysql_query($query, $dbh);
	$query  = "delete from rt_pruefling where 1";
	$result = mysql_query($query, $dbh);
	echo 'Daten wurden gelöscht!';
	mysql_close($dbh);
?>


