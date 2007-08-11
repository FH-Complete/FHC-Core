<?php
	include('../config.inc.php');
	// Verbindungsaufbau
	if (!$conn = @pg_pconnect(CONN_STRING)) 
	   	die("Es konnte keine Verbindung zum Server aufgebaut werden.");
	//Variablen setzen
	$stg_id=6;
	$stg_bez='ICSS';
	$datum_beginn='2003-09-01';
	$datum_ende='2004-02-09';
	// Stundenplantabelle abfragen
	$sql_query="SELECT count(*) AS stunden,unr, semester, lehrfach_id, lehrfachkurzbz, lektor_id, lektorkurzbz FROM vwstundenplan";
	$sql_query.=" WHERE studiengang_id=$stg_id AND datum>='$datum_beginn' AND datum<='$datum_ende'";
	$sql_query.=" GROUP BY unr, semester, lehrfach_id, lehrfachkurzbz, lektor_id, lektorkurzbz";
	$sql_query.=" ORDER BY semester,unr,lektor_id";
	//echo $sql_query."<br>";
	$result=pg_exec($conn, $sql_query);
	$num_rows=pg_numrows($result);
	// Daten in Array übernehmen
	for ($i=0;$i<$num_rows;$i++) 
	{
		$row=pg_fetch_object ($result, $i);
		$unterricht[$i]->lektor_kbz=$row->lektorkurzbz;
		$unterricht[$i]->lektor_id=$row->lektor_id;
		$unterricht[$i]->lehrfach_kbz=$row->lehrfachkurzbz;
		$unterricht[$i]->lehrfach_id=$row->lehrfach_id;
		$unterricht[$i]->unr=$row->unr;
		$unterricht[$i]->sem=$row->semester;
		$unterricht[$i]->stunden=$row->stunden;
	}		
?>
<html>
<head>
<title><?PHP echo $stg_bez; ?> - Lehrfachverteilung</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>
<body>
<H1><?PHP echo $stg_bez; ?> - &Uuml;berblick des Stundenplans</H1>
<H2>Von <?PHP echo $datum_beginn; ?> bis <?PHP echo $datum_ende; ?></H2>
<hr>
<table border="<?php echo $cfgBorder;?>">
<tr bgcolor="<?php echo $cfgThBgcolor; ?>"><th>Semester</th><th>UNr</th><th>Lehrfach</th><th>Lektor(en) [Stunden]</th></tr>
<?php
	for ($i=0; $i<$num_rows; $i++)
	{
		// Hintergrundfarbe wechseln
		$bgcolor = $cfgBgcolorOne;
     	$i % 2  ? 0: $bgcolor = $cfgBgcolorTwo;
		// Zeilenweise ausgeben
		echo "<tr bgcolor=$bgcolor>";
		echo '<td>'.$unterricht[$i]->sem.'</td><td>'.$unterricht[$i]->unr.'</td><td>'.$unterricht[$i]->lehrfach_kbz.'</td>';
		echo '<td>'.$unterricht[$i]->lektor_kbz.' ['.$unterricht[$i]->stunden.']';
		while ($unterricht[$i]->unr==$unterricht[$i+1]->unr)
			echo ', '.$unterricht[++$i]->lektor_kbz.' ['.$unterricht[$i]->stunden.']';
		echo '</td>';
		echo '</tr>';
	}
?>
</table>
</body>
</html>