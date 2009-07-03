<?php
	include('config.inc.php');
	if(!($dbh = @mysql_connect($dbhost, $dbuser, $dbpasswd))) 
	{
        die("Error: Cannot connect to database $dbhost");
	}
	mysql_select_db($dbname);
    
	$query  = "select id, bezeichnung, anzfragen, zeit from rt_gruppen where reihung<>0 order by reihung";
	$result = mysql_query($query, $dbh);

	while ($row = mysql_fetch_array($result))
    {
		$id = $row["id"];
		$bezeichnung = $row["bezeichnung"];
		$anzfragen = $row["anzfragen"];
		$zeit = $row["zeit"];
		echo "$id;$bezeichnung;$anzfragen;$zeit\$";
	}
	mysql_close($dbh);
?>
