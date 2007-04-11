<?php
require('../config.inc.php');
require('../../include/functions.inc.php');
 

// Verbindungsaufbau
if (!$conn = pg_pconnect(CONN_STRING))
	die("Es konnte keine Verbindung zum Server aufgebaut werden.");

// Vorkommende Gebiet laden
$sql_query="SELECT DISTINCT gebiet_id, gebiet FROM testtool.vw_auswertung";
//echo $sql_query;
if(!($result=pg_query($conn, $sql_query)))
    die(pg_errormessage($conn));
while ($row=pg_fetch_object($result))
{
	$gebiet[$row->gebiet_id]->name=$row->gebiet;
	$gebiet[$row->gebiet_id]->gebiet_id=$row->gebiet_id;
}
	
// Ergebnisse laden
$sql_query="SELECT * FROM testtool.vw_auswertung";
//echo $sql_query;
if(!($result=pg_query($conn, $sql_query)))
    die(pg_errormessage($conn));
    
while ($row=pg_fetch_object($result))
{
	$ergebnis[$row->person_id]->person_id=$row->person_id;
	$ergebnis[$row->person_id]->nachname=$row->nachname;
	$ergebnis[$row->person_id]->vorname=$row->vornamen;
	$ergebnis[$row->person_id]->gebdatum=$row->gebdatum;
	$ergebnis[$row->person_id]->geschlecht=$row->geschlecht;
	$ergebnis[$row->person_id]->idnachweis=$row->idnachweis;
	$ergebnis[$row->person_id]->registriert=$row->registriert;
	$ergebnis[$row->person_id]->stg_kurzbz=$row->stg_kurzbz;	
	$ergebnis[$row->person_id]->stg_bez=$row->stg_bez;
	$ergebnis[$row->person_id]->gruppe=$row->gruppe;
	$ergebnis[$row->person_id]->gebiet[$row->gebiet_id]->name=$row->gebiet;
	$ergebnis[$row->person_id]->gebiet[$row->gebiet_id]->anz_fragen=$row->anz_fragen;
	$ergebnis[$row->person_id]->gebiet[$row->gebiet_id]->abzug=$row->abzug;
	$ergebnis[$row->person_id]->gebiet[$row->gebiet_id]->anz_richtig=$row->anz_richtig;
	$ergebnis[$row->person_id]->gebiet[$row->gebiet_id]->anz_antworten=$row->anz_antworten;
	$ergebnis[$row->person_id]->gebiet[$row->gebiet_id]->anz_falsch=$row->anz_falsch;
	$ergebnis[$row->person_id]->gebiet[$row->gebiet_id]->punkte=$row->punkte;
	$ergebnis[$row->person_id]->gebiet[$row->gebiet_id]->prozent=$row->prozent;
}

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>

<head>
	<title>Testtool - Auswertung</title>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	<link rel="stylesheet" href="../../skin/cis.css">
</head>

<body>
<table>
  <tr>
		<th rowspan="2">ID</th><th rowspan="2">Nachname</th><th rowspan="2">Vornamen</th>
		<th rowspan="2">GebDatum</th><th rowspan="2">G</th><th rowspan="2">IdNachweis</th>
		<th rowspan="2">Registriert</th><th rowspan="2">STG</th><th rowspan="2">Studiengang</th>
		<th rowspan="2">Grp</th>
		<?php
		foreach ($gebiet AS $gbt)
			echo "<th colspan='2'>$gbt->name</th>";
		?>
  </tr>
   <tr>
		<?php
		foreach ($gebiet AS $gbt)
			echo "<th>Punkte</th><th>Prozent</th>";
		?>
  </tr>
  <tr>
  	</th><th>
  </tr>
  <?php
  	foreach ($ergebnis AS $erg)
  	{
  		echo "<tr><td>$erg->person_id</td><td>$erg->nachname</td><td>$erg->vorname</td><td>$erg->gebdatum</td><td>$erg->geschlecht</td>
  					<td>$erg->idnachweis</td><td>$erg->registriert</td><td>$erg->stg_kurzbz</td><td>$erg->stg_bez</td><td>$erg->gruppe</td>";
  		foreach ($gebiet AS $gbt)
			echo '<td>'.$erg->gebiet[$gbt->gebiet_id]->punkte.'</td><td>'.$erg->gebiet[$gbt->gebiet_id]->prozent.' %</td>';
  		echo '</tr>';
  	}
  ?>
</table>
</body>
</html>
