
<html>
<head>
	<title>Stundenplan säubern</title>
</head>
<body>
<h3>Stundenplan s&auml;ubern</h3>
<p>Folgende Eintr&auml;ge kommen doppelt vor:</p>
<p> 
<?php
// Stundenplantabelle aus Datenbank holen
mysql_connect($cfgServer['host'].":".$cfgServer['port'], $cfgServer['user'], $cfgServer['password']) or die ("Unable to connect to SQL-Server");
@mysql_select_db("FHDaten") or die ("Unable to select database");
$mysql_query="SELECT * FROM stundenplan ORDER BY studiengang_id, lehrverband_id, lektor_id, lehrfach_id, ort_id, woche, tag, stundentafel_id, typ_id"; 

$result=mysql_query($mysql_query);
$num_rows=mysql_num_rows($result);
$num_fields=mysql_num_fields($result);
$anz=0;

// Tabelle für vergleiche vorbereiten
for ($i=0; $i<$num_rows; $i++) 
{
	$row=mysql_fetch_row($result);
	$help_str="";
	$stdplan_table[$i][0]=$row[0];
	for ($j=1; $j<$num_fields; $j++)
		$help_str.=$row[$j];
	$stdplan_table[$i][1]=$help_str;
	//echo "<br>helpstr=".$help_str." - table0=".$stdplan_table[$i][0]." - table1=".$stdplan_table[$i][1];
}

//Sortieren
for ($i=1; $i<$num_rows; $i++) 
	{
		$erg=strcmp($stdplan_table[$i-1][1],$stdplan_table[$i][1]);
		if ($erg==0)
		{
			echo $stdplan_table[$i][1]."<br>";
			$mysql_query="DELETE FROM stundenplan WHERE id=".$stdplan_table[$i][0];
			$result=mysql_query($mysql_query);
			$anz++;
		}
	}
	
echo "$anz doppelte Einträge wurden gefunden und gelöscht.<br>$num_rows Einträge gesamt.";

?>
</p>
<hr>
Erstellt am 28. Mai 2001 von <a href="mailto:humer@technikum-wien.at">Christian Humer</a><br>
Letzte Änderung: 18. Juni 2001 von <a href="mailto:humer@technikum-wien.at">Christian Humer</a>
</body>
</html>
