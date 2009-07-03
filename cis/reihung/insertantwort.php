<?php
	include('config.inc.php');
	if(!($dbh = @mysql_connect($dbhost, $dbuser, $dbpasswd))) 
	{
        die("Error: Cannot connect to database $dbhost");
	}
	mysql_select_db($dbname);
    
	$query = "insert into rt_antwort (gruppe, pruefling, nummer, antwort) ";
	$query.= "values ($gruppe, $pruefling, $nummer, '$antwort')";

	$result = mysql_query($query, $dbh);

	$id = mysql_insert_id($dbh);
	echo $id;

	mysql_close($dbh);
?>
