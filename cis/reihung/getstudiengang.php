<?php
	include('config.inc.php');
	if(!($dbh = @mysql_connect($dbhost, $dbuser, $dbpasswd))) 
	{
        die("Error: Cannot connect to database $dbhost");
	}
	mysql_select_db($dbname);
    
	$query  = "select name from rt_studiengang";
	$result = mysql_query($query, $dbh);

	while ($row = mysql_fetch_array($result))
    {
		$name = $row["name"];
		echo "$name;";
	}
	mysql_close($dbh);
?>
