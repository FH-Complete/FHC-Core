<?php
	include('config.inc.php');
	if(!($dbh = @mysql_connect($dbhost, $dbuser, $dbpasswd))) 
	{
        die("Error: Cannot connect to database $dbhost");
	}
	mysql_select_db($dbname);
    
	$query  = "select id from rt_studiengang where name='$studiengang'";
	$result = mysql_query($query, $dbh);
    $row = mysql_fetch_array($result);
    $studiengangID = $row["id"];
	
	$query  = "insert into rt_pruefling(name, vorname, gebdatum, gruppe, studiengangID, datum) ";
	$query .= "values ('$name', '$vorname', '$gebdatum', '$gruppe', $studiengangID, NOW())";

	$result = mysql_query($query, $dbh);
	$id = mysql_insert_id($dbh);
	echo "$id";

	mysql_close($dbh);
?>
