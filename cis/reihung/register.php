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

	$query  = "select id, anzfragen from rt_gruppen where reihung<>0 order by reihung";
	$result = mysql_query($query, $dbh);

	while ($row = mysql_fetch_array($result))
    {
		$gid = $row["id"];
		$anzfragen = $row["anzfragen"];

		for ($i=1;$i<=$anzfragen;$i++)
		{
			$query1  = "insert into rt_antwort(gruppenID,prueflingID,nummer,antwort) values ($gid,$id,$i,' ')";
			$result1 = mysql_query($query1, $dbh);
		}
	}
	echo "$id";

	mysql_close($dbh);
?>
