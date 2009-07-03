<?php
	include('config.inc.php');
	if(!($dbh = @mysql_connect($dbhost, $dbuser, $dbpasswd))) 
	{
        die("Error: Cannot connect to database $dbhost");
	}
	mysql_select_db($dbname);
    
	$query  = "select antwort from rt_antwort where ";
	$query .= "gruppenID=$gruppe and prueflingID=$pruefling";
	$query .= " order by nummer ASC";

	$result = mysql_query($query, $dbh);

	while ($row = mysql_fetch_array($result))
 	{
		$antwort = $row[0];
 
		echo "$antwort;";	
	}

	mysql_close($dbh);
?>
