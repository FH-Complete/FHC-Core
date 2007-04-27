<?php
	/**
	 *	Raumauslastung
	 *
	 */

	include('../config.inc.php');
	$raum=array();

	if (isset($_POST['datum_beginn']))
		$datum_beginn=$_POST['datum_beginn'];
	else
		$datum_beginn='2007-02-12';
	if (isset($_POST['datum_ende']))
		$datum_ende=$_POST['datum_ende'];
	else
		$datum_ende='2007-06-30';
	if (isset($_POST['stunde_beginn']))
		$stunde_beginn=$_POST['stunde_beginn'];
	else
		$stunde_beginn=12;
	if (isset($_POST['stunde_ende']))
		$stunde_ende=$_POST['stunde_ende'];
	else
		$stunde_ende=16;

	if (!$conn = pg_pconnect(CONN_STRING))
	   	die("Es konnte keine Verbindung zum Server aufgebaut werden.");

	//Stundenplandaten holen
	$sql_query="SELECT DISTINCT datum,stunde,ort_kurzbz, EXTRACT(DOW FROM datum) AS tag FROM lehre.tbl_stundenplan
					WHERE datum>='$datum_beginn' AND datum<='$datum_ende' AND stunde>=$stunde_beginn AND stunde<=$stunde_ende
					ORDER BY ort_kurzbz";
	if(! $result=pg_query($conn, $sql_query))
		die(pg_last_error($conn));
	//echo $sql_query;
	//Aufbereitung
	while ($row=pg_fetch_object($result))
	{
		$raum[$row->ort_kurzbz]->ort=$row->ort_kurzbz;
		if (!isset($raum[$row->ort_kurzbz]->last[$row->tag][$row->stunde]->anzahl))
			$raum[$row->ort_kurzbz]->last[$row->tag][$row->stunde]->anzahl=1;
		else
			$raum[$row->ort_kurzbz]->last[$row->tag][$row->stunde]->anzahl++;
	}

?>

<html>
<head>
<title>Profil</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
</head>

<body>
<form method="POST">
	Beginn:<input name="datum_beginn" value="<?php echo $datum_beginn; ?>" size="8" />
	Ende:<input name="datum_ende" value="<?php echo $datum_ende; ?>" size="8" />
	&nbsp;&nbsp;&nbsp;&nbsp;
	Stunde -> von:<input name="stunde_beginn" value="<?php echo $stunde_beginn; ?>" size="2" />
	bis:<input name="stunde_ende" value="<?php echo $stunde_ende; ?>" size="2" />
	<input type="submit">
</form>
<h2> Raumauslastung vom <?PHP echo $datum_beginn.' - '.$datum_ende; ?></h2>
<TABLE width="100%" border="1" cellspacing="0" cellpadding="0">
	<TR>
    <?php
    	$span=$stunde_ende-$stunde_beginn+1;
	echo "<th rowspan='2'>Ort</th><th colspan='$span'>Montag</th><th colspan='$span'>Dienstag</th><th colspan='$span'>Mittwoch</th>
		<th colspan='$span'>Donnerstag</th><th colspan='$span'>Freitag</th><th colspan='$span'>Samstag</th>";
	?>
    </TR>
	<TR>
    <?php
	echo '';
	for ($t=1;$t<7;$t++)
		for ($s=$stunde_beginn;$s<=$stunde_ende; $s++)
		{
			echo "<th>$s</th>";
		}
	?>
    </TR>
	<?php
	foreach ($raum AS $ort)
	{
		echo '<TR><TD>'.$ort->ort.'</TD>';
	  	for ($t=1;$t<7;$t++)
			for ($s=$stunde_beginn;$s<=$stunde_ende; $s++)
			{
				if (!isset($ort->last[$t][$s]->anzahl))
					$ort->last[$t][$s]->anzahl=0;
				echo '<TD>';
				echo $ort->last[$t][$s]->anzahl;
				echo '</TD>';
			}
		echo '</TR>';
	}
	?>
</TABLE>
</body>
</html>
