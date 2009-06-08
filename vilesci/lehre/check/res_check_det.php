<?php
	include('../../config.inc.php');
	$conn=pg_connect(CONN_STRING);

	//Stundenplandaten ermitteln welche mehrfach vorkommen
	$sql_query="SELECT * FROM lehre.vw_reservierung WHERE datum='$datum' AND stunde=$stunde AND ort_kurzbz='$ort_kurzbz'";
	//echo $sql_query."<br>";
	$result=pg_exec($conn, $sql_query);
	$num_rows=pg_numrows($result);

$cfgBorder=1;
$cfgBgcolorOne='liste0';
$cfgBgcolorTwo='liste1';
?>

<html>
<head>
<title>Reservierung Check Details</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<LINK rel="stylesheet" href="../../../skin/vilesci.css" type="text/css">
</head>
<body>
<H1>Mehrfachbelegungen in Reservierungen Detailansicht</H1>
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
		echo '<tr class="'.$bgcolor.'">';
	    for ($i=0; $i<$num_fields; $i++)
			echo "<td>$row[$i]&nbsp;</td>";
		echo "<td><a href=\"res_check_delete.php?id=$row[0]\">Delete</a></td>";
		echo "<td><a href=\"res_check_mail.php?id=$row[0]\">Mail&Delete</a></td>";

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