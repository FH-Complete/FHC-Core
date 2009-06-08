<?php
	include('../../config.inc.php');
	include('wochendatum.inc.php');
	if (!($conn=pg_connect(CONN_STRING)))
		die ("No connection to Database!");
	$tagsek=86400;
?>

<html>
<head>
<title>Check ID</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<LINK rel="stylesheet" href="../../../include/styles.css" type="text/css">
</head>
<body class="background_main">
<H1>Stundenplan wird übertragen</H1>

<?php
	// Studiengänge abfragen
	$sql_query="SELECT id, unr, wochentag, stunde, ort_kurzbz, lehrfach_nr, lektor_uid, jahreswochen, studiengang_kz, semester, verband, gruppe FROM untis
		WHERE ort_kurzbz IS NOT NULL AND lehrfach_nr>0 AND lektor_uid IS NOT NULL AND studiengang_kz IS NOT NULL AND lehrfach NOT LIKE '\\\\_%'";
	//echo $sql_query;
	$result=pg_exec($conn, $sql_query);
	$num_rows=pg_numrows($result);
	echo $num_rows.' Rows will be moved<BR>';
	flush();

	for ($i=0; $i<$num_rows; $i++)
	{
		if (($i%50)==0)
			echo '<BR>'.$i;
		echo '.';
		flush();
		$row=pg_fetch_object($result,$i);
		for ($w=1; $w<=53; $w++)
		{
			if (substr($row->jahreswochen,$w-1,1)=='1')
			{
				$date=$week_date[$w]+($tagsek*($row->wochentag-1));
				$date=getdate($date);
				$tag=$date[mday];
				$monat=$date[mon];
				$jahr=$date[year];
				$date=$jahr.'-'.$monat.'-'.$tag;
				$sql_query="INSERT INTO tbl_stundenplan (unr, studiengang_kz, semester, verband, gruppe, ort_kurzbz, datum, stunde, lehrfach_nr, uid)
					VALUES ('$row->unr',$row->studiengang_kz,$row->semester,'$row->verband','$row->gruppe', '$row->ort_kurzbz', '$date', $row->stunde, $row->lehrfach_nr, '$row->lektor_uid')";
				$result_insert=pg_exec($conn, $sql_query);
			}
		}
		$sql_query="DELETE FROM untis WHERE id=$row->id";
		$result_insert=pg_exec($conn, $sql_query);
	}
?>
<BR>Finished!
</body>
</html>