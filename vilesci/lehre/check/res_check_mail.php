<?php
	include('../../config.inc.php');
	$conn=pg_connect($conn_string);

	$sql_query="SELECT reservierung.id, datum, stunde_id, ort.kurzbz AS ortkurzbz, lektor.kurzbz AS lektorkurzbz, reservierung.titel, reservierung.beschreibung, lektor.uid FROM reservierung, ort, lektor WHERE reservierung.id=$id AND ort_id=ort.id AND lektor_id=lektor.id";
	//echo $sql_query."<br>";
	$result=pg_exec($conn, $sql_query);
	$num_rows=pg_numrows($result);
	if ($num_rows==1)
	{	
		$row=pg_fetch_object($result,0);
		$text="Dies ist eine automatische eMail!\r\rAufgrund einer Stundenplankollision wurde folgende Reservierung gelöscht:\r\r";
		$text.="Datum:\t$row->datum\rStunde:\t$row->stunde_id\rOrt:\t$row->ortkurzbz\rTitel:\t$row->titel\r\r";
		$text.="Wir bitten um Verständnis.";
		$adress=$row->uid.'@technikum-wien.at';
		if (mail($adress,"Stundenplankollision",$text,"From: stpl@technikum-wien.at"))
			$sendmail=true;
		else
			$sendmail=false;
	}
	else
		$sendmail=false;
	//Stundenplandaten ermitteln welche mehrfach vorkommen
	$sql_query="DELETE FROM reservierung WHERE id=$id";
	//echo $sql_query."<br>";
	$result=pg_exec($conn, $sql_query);
	$num_rows=pg_numrows($result);
?>

<html>
<head>
<title>Reservierung Check Delete</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<LINK rel="stylesheet" href="../../../include/styles.css" type="text/css">
</head>
<body>
<H1>Mehrfachbelegungen in Reservierung l&ouml;schen</H1>
<?php 
if ($sendmail)
	echo 'Mail wurde verschickt an '.$adress.'!<br>';
else
	echo "Mail konnte nicht verschickt werden!<br>";

if ($result)
	echo "Datensatz wurde erfolgreich gel&ouml;scht!";
else
	echo "Es ist ein Fehler aufgetreten!";
?><br>
<a href="res_check.php"><br>
<br>
Zur&uuml;ck zu &Uuml;bersicht</a>
</body>
</html>