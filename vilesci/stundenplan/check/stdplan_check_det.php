<?php
	include('../../config.inc.php');
	$conn=pg_connect($conn_string);

	//Stundenplandaten ermitteln welche mehrfach vorkommen
	$sql_query="SELECT id, datum, stunde_id, semester, verband, gruppe, ortkurzbz, stgkurzbz, lehrfachkurzbz, lektorkurzbz FROM vwstundenplan WHERE datum='$datum' AND stunde_id=$stunde_id AND ort_id=$ort_id AND studiengang_id=$studiengang_id AND semester='$semester' AND verband='$verband' AND gruppe='$gruppe'";
	echo $sql_query."<br>";
	$result=pg_exec($conn, $sql_query);
	$num_rows=pg_numrows($result);
?>

<html>
<head>
<title>Stundenplan Check Details</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>
<body>
<H1>Mehrfachbelegungen Detailansicht</H1>
<table border="<?php echo $cfgBorder;?>">
<tr>
<?php 
if ($num_rows!=0)
{
	$num_fields=pg_numfields($result);
	$foo = 0;
	for ($i=0;$i<$num_fields; $i++)
	    echo "<th>".pg_fieldname($result,$i)."</th>";		
	for ($j=0; $j<$num_rows;$j++)
	{
		$row=pg_fetch_row($result,$j);
		$bgcolor = $cfgBgcolorOne;
		$foo % 2  ? 0: $bgcolor = $cfgBgcolorTwo;
		echo "<tr bgcolor=$bgcolor>";
	    for ($i=0; $i<$num_fields; $i++)
			echo "<td bgcolor=$bgcolor>$row[$i]</td>";
		echo "<td><a href=\"stdplan_check_delete.php?id=$row[0]\">Delete</a><td>";
	    echo "</tr>\n";
		$foo++;
	}
}
else
	echo "Kein Eintrag gefunden!";
?>
</table>
</body>
</html>