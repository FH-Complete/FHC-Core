<?php
	include('config.inc.php');
	if(!($dbh = @mysql_connect($dbhost, $dbuser, $dbpasswd))) 
	{
        die("Error: Cannot connect to database $dbhost");
	}
	mysql_select_db($dbname);
    
	$query  = "update rt_antwort set antwort='$antwort' where ";
	$query .= "gruppenID=$gruppe and prueflingID=$pruefling and nummer=$nummer";

	$result = mysql_query($query, $dbh);

	echo "$antwort";

	mysql_close($dbh);
?>
