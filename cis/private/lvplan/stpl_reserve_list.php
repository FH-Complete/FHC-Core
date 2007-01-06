<?php
	require_once('../../config.inc.php');

	$uid=$REMOTE_USER;
	//$uid='pam';

	if (isset($_GET['id']))
		$id=$_GET['id'];

	// Datenbankverbindung
	if (!$conn = pg_pconnect(CONN_STRING))
		die('Es konnte keine Verbindung zum Server aufgebaut werden.');
	// Datums Format und search_path
	if(!$erg_std=pg_query($conn, "SET datestyle TO ISO; SET search_path TO campus;"))
		die(pg_last_error($conn));

	if (isset($id))
	{
		$sql_query="DELETE FROM tbl_reservierung WHERE reservierung_id=$id";
		$erg=pg_exec($conn, $sql_query);
	}

	//Aktuelle Reservierungen abfragen.
	$datum=mktime();
	$datum=date("Y-m-d",$datum);
	$sql_query="SELECT * FROM vw_reservierung WHERE datum>='$datum' ";
	$sql_query.=" ORDER BY  datum, titel, ort_kurzbz, stunde";
	$erg_res=pg_exec($conn, $sql_query);
	$num_rows_res=pg_numrows($erg_res);
?>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	<title>Reservierungsliste</title>
	<link rel="stylesheet" href="../../../skin/cis.css" type="text/css">
</head>
<body>
	<H2><table width="100%" border="0" cellpadding="0" cellspacing="0">
		<tr>
		<td>&nbsp;<a href="index.php">Lehrveranstaltungsplan</a> &gt;&gt; Reservierungen</td>
		<td align="right"><A href="help/index.html" class="hilfe" target="_blank">HELP&nbsp;</A></td>
		</tr>
		</table>
	</H2>
	<?php
	if ($num_rows_res>0)
	{
		echo '<table border="0">';
		echo '<tr class="liste"><th>Datum</th><th>Titel</th><th>Stunde</th><th>Ort</th><th>Person</th><th>Beschreibung</th></tr>';
		for ($i=0; $i<$num_rows_res; $i++)
		{
			$zeile=$i % 2;
			$id=pg_result($erg_res,$i,"reservierung_id");
			$datum=pg_result($erg_res,$i,"datum");
			$titel=pg_result($erg_res,$i,"titel");
			$stunde=pg_result($erg_res,$i,"stunde");
			$ort_kurzbz=pg_result($erg_res,$i,"ort_kurzbz");
			$pers_uid=pg_result($erg_res,$i,"uid");
			//$lektor_kurzbz=pg_result($erg_res,$i,"lektor_kurzbz");
			$beschreibung=pg_result($erg_res,$i,"beschreibung");
			echo '<tr class="liste'.$zeile.'">';
			echo '<td>'.$datum.'</td>';
			echo '<td>'.$titel.'</td>';
			echo '<td>'.$stunde.'</td>';
			echo '<td>'.$ort_kurzbz.'</td>';
			echo '<td>'.$pers_uid.'</td>';
			echo '<td>'.$beschreibung.'<a  name="liste'.$i.'">&nbsp;</a></td>';
			$z=$i-1;
			if (($pers_uid==$uid)||($uid=='pam')||($uid=='kindlm')||($uid=='dvorak')||($uid=='betty'))
				echo '<td><A href="stpl_reserve_list.php?id='.$id.'#liste'.$z.'">Delete</A></td>';
			echo '</tr>';
		}
		echo '</table>';
	}
?>
</body>
</html>