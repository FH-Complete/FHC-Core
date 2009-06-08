<?php
	include('../../config.inc.php');
	$conn=pg_connect($conn_string);

	//Stundenplandaten ermitteln welche mehrfach vorkommen
	$sql_query="SELECT count(*), datum, stunde_id, ort_id, studiengang_id, semester, verband, gruppe FROM stundenplan GROUP BY datum, stunde_id, ort_id, studiengang_id, semester, verband, gruppe HAVING (count(*)>1) ORDER BY datum, stunde_id, ort_id LIMIT 20";
	//echo $sql_query."<br>";
	$result=pg_exec($conn, $sql_query);
	$num_rows=pg_numrows($result);
?>

<html>
<head>
<title>Stundenplan Check</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
</head>
<body>
<H1>Mehrfachbelegungen</H1>
<table border="<?php echo $cfgBorder;?>">
<tr>
<?php 
if ($result!=0)
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
		echo "<td><a href=\"stdplan_check_det.php?datum=$row[1]&stunde_id=$row[2]&ort_id=$row[3]&studiengang_id=$row[4]&semester=$row[5]&verband=$row[6]&gruppe=$row[7]\">Details</a><td>";
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