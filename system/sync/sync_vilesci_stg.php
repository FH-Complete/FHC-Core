<?php
include('../../vilesci/config.inc.php');
include('../../include/studiengang.class.php');

$conn=pg_connect(CONN_STRING);
$conn_vilesci=pg_connect(CONN_STRING_VILESCI);
$adress='pam@technikum-wien.at';

// Encoding fuer VileSci
$qry = "SET CLIENT_ENCODING TO 'UNICODE';";
if(!pg_query($conn_vilesci,$qry))
{
	$this->errormsg	 = "Encoding konnte nicht gesetzt werden";
	return false;
}

//Erhalter anlegen
$result=pg_query($conn,  "INSERT INTO public.tbl_erhalter (erhalter_kz, kurzbz, bezeichnung, dvr, logo, zvr) VALUES('5', 'TW','Technikum Wien', '0928381',null,'074476426');");

/*************************
 * VileSci-Synchronisation
 */
//Studiengaenge vom VileSci holen
$sql_query='SELECT * FROM tbl_studiengang';
//echo $sql_query.'<br>';
$stg_vilesci=pg_exec($conn_vilesci, $sql_query);
//pg_query($conn, "SET CLIENT_ENCODING TO 'LATIN1';");
while ($stg=pg_fetch_object($stg_vilesci))
{
	$sql_query="INSERT INTO tbl_studiengang(studiengang_kz, kurzbz, kurzbzlang, bezeichnung, typ, farbe, email, max_semester, max_verband, max_gruppe, erhalter_kz)
	            VALUES ($stg->studiengang_kz,'".substr($stg->kurzbz,0,3)."', '$stg->kurzbzlang','$stg->bezeichnung',
					'$stg->typ','$stg->farbe','$stg->email',$stg->max_semester,'$stg->max_verband','$stg->max_gruppe',5)";
	if (!$result=@pg_exec($conn, $sql_query))
		echo pg_last_error($conn).'<br>--'.$sql_query.'<br>';

}

?>

<html>
<head>
<title>FAS-Synchro mit VileSci</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>
<body>
<h3>Studiengaenge werden synchronisiert!</h3>
<?php

?>
</body>
</html>
