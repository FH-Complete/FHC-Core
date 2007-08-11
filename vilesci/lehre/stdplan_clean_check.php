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
$mysql_query="SELECT * FROM stundenplan ORDER BY id";
$result=mysql_query($mysql_query);
$num_rows=mysql_num_rows($result);
$num_fields=mysql_num_fields($result);
$anz=0;

// Tabelle für vergleiche vorbereiten
for ($i=0; $i<$num_rows; $i++)
{
	$row=mysql_fetch_row($result);
	$help_str="";
	$stdplan_table[$i][1]="";
	for ($j=0; $j<$num_fields; $j++)
	{
		if ($j==0)
			$stplan_table[$i][0]=$row[$j];
		else
			$help_str.=$row[$j];
	}
	$stdplan_table[$i][1]=$help_str;
}

// Tabelle auf doppelte Einträge durchsuchen
$equl=false;
for ($i=1; $i<$num_rows; $i++)
{
	if (!strcmp($stdplan_table[$i-1][1],$stdplan_table[$i][1]))
	{
		echo $stdplan_table[$i][1],"<br>";
		if (!$equl)
		{
			$equl=true;
			echo $stdplan_table[$i][1],"<br>";
			$anz++;
		}
	}
	else
		$equl=false;
}
echo "$anz doppelte Einträge wurden gefunden und gelöscht.<br>$num_rows Einträge gesamt.";
/*$foo = 0;
for ($j=0; $j<$num_rows; $j++)
{
	$bestell_id=mysql_result($result, $j, "ID");
	$bestell_nr=mysql_result($result, $j, "BestellNr");
	$bgcolor = $cfgBgcolorOne;
	$foo % 2  ? 0: $bgcolor = $cfgBgcolorTwo;
	echo "<tr bgcolor=$bgcolor>";
	echo "<td bgcolor=$bgcolor><div align=\"right\">$bestell_id</div></td>";
	echo "<td bgcolor=$bgcolor><div align=\"right\">$bestell_nr</div></td>";
	echo "<td><a href=\"bestell_det_ueb.php3?bestellid=$bestell_id\">Details</A></td>";
	$body.="$bestell_nr: \t http://data.technikum-wien.at/bestellungen/bestell_det_ueb.php3?bestellid=$bestell_id\n";
	echo "</tr>\n";
	$foo++;
} */
?>
</p>
<hr>
Erstellt am 28. Mai 2001 von <a href="mailto:humer@technikum-wien.at">Christian Humer</a><br>
Letzte Änderung: 28. Mai 2001 von <a href="mailto:humer@technikum-wien.at">Christian Humer</a>
</body>
</html>
