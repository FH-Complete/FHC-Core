<?php
	include('config.inc.php');
	if(!($dbh = @mysql_connect($dbhost, $dbuser, $dbpasswd))) 
	{
        die("Error: Cannot connect to database $dbhost");
	}
	mysql_select_db($dbname);
    
	$query  = "select p.id, p.name, p.vorname, p.gebdatum, p.gruppe, s.name ";
	$query .= "from rt_pruefling p, rt_studiengang s where p.id=$id and p.studiengangID=s.id";
	$result = mysql_query($query, $dbh);
    
	if ($row = mysql_fetch_array($result))
 	{
		$pid = $row[0];
		$pname = $row[1];
		$pvorname = $row[2];
		$pgebdatum = $row[3];
		$pgruppe = $row[4];
		$sname = $row[5];
 
		echo "$pid;$pname;$pvorname;$pgebdatum;$pgruppe;$sname";	
	}
	else
	{
		echo "0";
	}
	mysql_close($dbh);
?>
