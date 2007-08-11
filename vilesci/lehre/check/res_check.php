<?php
	include('../../config.inc.php');
	$conn=pg_connect(CONN_STRING);
?>

<html>
<head>
<title>Stundenplan Check</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<LINK rel="stylesheet" href="../../../include/styles.css" type="text/css">
</head>
<body>
<H1>Mehrfachbelegungen in Reservierung</H1>
<H2>Doppelbelegungen </H2>
<table border="<?php echo $cfgBorder;?>">
<tr>
<?php
	//Reservierungsdaten ermitteln welche mehrfach vorkommen
	$sql_query="SELECT count(*), datum, stunde, ort_kurzbz FROM tbl_reservierung GROUP BY datum, stunde, ort_kurzbz HAVING (count(*)>1) ORDER BY datum, stunde, ort_kurzbz LIMIT 20";
	//echo $sql_query."<br>";
	$result=pg_exec($conn, $sql_query);
	$num_rows=pg_numrows($result);
	if ($num_rows>0)
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
			echo "<td><a href=\"res_check_det.php?datum=$row[1]&stunde=$row[2]&ort_kurzbz=$row[3]\">Details</a></td>";
	    	echo "</tr>\n";
			$foo++;
		}
	}
	else
		echo "Keine Doppelbelegungen gefunden!";
?>
</table>
<H2>Kollisionen mit Stundenplan</H2>
<?php
	flush();
	//Reservierungsdaten ermitteln welche mit Stundenplan kollidieren
	$sql_query="SELECT reservierung_id, datum, stunde, ort_kurzbz, uid FROM tbl_reservierung ORDER BY datum, stunde, ort_kurzbz";
	//echo $sql_query."<br>";
	$result_res=pg_exec($conn, $sql_query);
	$num_rows_res=pg_numrows($result_res);
	if ($num_rows_res>0)
	{
		echo $num_rows_res.' Einträge werden überprüft .';
		$foo = 0;
		for ($r=0;$r<$num_rows_res;$r++)
		{
			$row_res=pg_fetch_object($result_res,$r);
			$sql_query="SELECT * FROM vw_stundenplan WHERE datum='$row_res->datum' AND stunde=$row_res->stunde AND ort_kurzbz='$row_res->ort_kurzbz'";
			//echo $sql_query."<br>";
			$result=pg_exec($conn, $sql_query);
			$num_rows=pg_numrows($result);
			//echo $num_rows;
			if ($num_rows>0)
			{
				echo '<table border="'.$cfgBorder.'"><tr>';
				$num_fields=pg_numfields($result);
				echo "<th></th>";
				for ($i=0;$i<$num_fields; $i++)
	    			echo "<th>".pg_fieldname($result,$i)."</th>";
				echo "</tr>\n";
				for ($j=0; $j<$num_rows;$j++)
				{
					$row=pg_fetch_row($result,$j);
					$rowo=pg_fetch_object($result,$j);
					$bgcolor = $cfgBgcolorOne;
					$foo % 2  ? 0: $bgcolor = $cfgBgcolorTwo;
					echo "<tr bgcolor=$bgcolor>";
					echo "<td><a href=\"res_check_det.php?datum=$rowo->datum&stunde=$rowo->stunde&ort_kurzbz=$rowo->ort_kurzbz\">Reservierung</a></td>";
	    			for ($i=0; $i<$num_fields; $i++)
						echo "<td bgcolor=$bgcolor>$row[$i]</td>";
					echo "</tr>\n";
					$foo++;
				}
				echo '</table>';
				flush();
			}
			if ($r%500==0)
				echo '<BR>'.$r;
			if ($r%10==0)
			{
				echo '.';
				flush();
			}
		}
	}
	else
		echo "Kein Eintrag gefunden!";
?>

</body>
</html>