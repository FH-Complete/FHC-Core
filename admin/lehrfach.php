<html>
<head>
<title>Abgleich der Lehrfaecher</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>
<body>

<?php
	include('../vilesci/config.inc.php');
	$conn=pg_connect(CONN_STRING);
	$sql_query='SELECT tbl_stundenplan.*, tbl_lehrfach.lehrform_kurzbz, tbl_lehrfach.kurzbz AS lehrfach, tbl_lehrfach.bezeichnung, tbl_lehrfach.farbe
			FROM tbl_stundenplan, tbl_lehrfach WHERE tbl_stundenplan.lehrfach_nr=tbl_lehrfach.lehrfach_nr
			AND (tbl_stundenplan.studiengang_kz!=tbl_lehrfach.studiengang_kz
				OR tbl_stundenplan.semester!=tbl_lehrfach.semester)';  //LIMIT 10000
	//echo $sql_query."<br>";
	$result=pg_query($conn, $sql_query);
	$num_rows=pg_numrows($result);
	for ($i=0;$i<$num_rows;$i++)
	{
		$row=pg_fetch_object($result,$i);
		$sql_query="SELECT lehrfach_nr FROM tbl_lehrfach WHERE studiengang_kz=$row->studiengang_kz
			AND semester=$row->semester AND kurzbz='$row->lehrfach' AND lehrform_kurzbz='$row->lehrform_kurzbz'";
		//echo $sql_query."<br>";
		if (!$res=pg_exec($conn, $sql_query))
			echo pg_last_error($conn).'<br>';
		else
			if (pg_numrows($res)>=1)
			{
				$lehrfach_nr=pg_fetch_object($res);
				$lehrfach_nr=$lehrfach_nr->lehrfach_nr;
				$sql_query="update tbl_stundenplan set lehrfach_nr=$lehrfach_nr WHERE stundenplan_id=$row->stundenplan_id";
				//echo $sql_query."<br>";
				if (!$ergebniss=pg_query($conn, $sql_query))
					echo pg_last_error($conn).'<br>';
			}
			else
			{
				$sql_query="INSERT INTO tbl_lehrfach (studiengang_kz,semester,kurzbz,lehrform_kurzbz,bezeichnung,fachbereich_id,farbe) VALUES ($row->studiengang_kz,$row->semester,'$row->lehrfach','$row->lehrform_kurzbz','$row->bezeichnung',0,'$row->farbe');";
				echo $sql_query.'<BR>';
				if (!$ergebniss=pg_query($conn, $sql_query))
					echo pg_last_error($conn).'<br>';
			}
	}
	echo $num_rows.' Datensaetze abgeglichen! Fertig<br>';

?>

Datenabgleich abgeschlossen!
</body>
</html>
