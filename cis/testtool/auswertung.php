<?php
require('../config.inc.php');
require('../../include/functions.inc.php');

function sortByField($multArray,$sortField,$desc=true)
{
	$tmpKey='';
    $ResArray=array();

    $maIndex=array_keys($multArray);
    $maSize=count($multArray)-1;

    for($i=0; $i < $maSize ; $i++)
    {
    	$minElement=$i;
    	$tempMin=$multArray[$maIndex[$i]]->$sortField;
    	$tmpKey=$maIndex[$i];
    	for($j=$i+1; $j <= $maSize; $j++)
    		if($multArray[$maIndex[$j]]->$sortField < $tempMin )
    		{
   				$minElement=$j;
    		    $tmpKey=$maIndex[$j];
    		    $tempMin=$multArray[$maIndex[$j]]->$sortField;
    		}
    	$maIndex[$minElement]=$maIndex[$i];
    	$maIndex[$i]=$tmpKey;
    }

    if($desc)
    	for($j=0;$j<=$maSize;$j++)
    		$ResArray[$maIndex[$j]]=$multArray[$maIndex[$j]];
    else
        for($j=$maSize;$j>=0;$j--)
            $ResArray[$maIndex[$j]]=$multArray[$maIndex[$j]];

    return $ResArray;
}


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
	$ergebnis[$row->pruefling_id]->pruefling_id=$row->pruefling_id;
	$ergebnis[$row->pruefling_id]->nachname=$row->nachname;
	$ergebnis[$row->pruefling_id]->vorname=$row->vorname;
	$ergebnis[$row->pruefling_id]->gebdatum=$row->gebdatum;
	$ergebnis[$row->pruefling_id]->geschlecht=$row->geschlecht;
	$ergebnis[$row->pruefling_id]->idnachweis=$row->idnachweis;
	$ergebnis[$row->pruefling_id]->registriert=$row->registriert;
	$ergebnis[$row->pruefling_id]->stg_kurzbz=$row->stg_kurzbz;
	$ergebnis[$row->pruefling_id]->stg_bez=$row->stg_bez;
	$ergebnis[$row->pruefling_id]->gruppe=$row->gruppe_kurzbz;
	$ergebnis[$row->pruefling_id]->gebiet[$row->gebiet_id]->name=$row->gebiet;
	$ergebnis[$row->pruefling_id]->gebiet[$row->gebiet_id]->anz_fragen=$row->anz_fragen;
	$ergebnis[$row->pruefling_id]->gebiet[$row->gebiet_id]->abzug=$row->abzug;
	$ergebnis[$row->pruefling_id]->gebiet[$row->gebiet_id]->anz_richtig=$row->anz_richtig;
	$ergebnis[$row->pruefling_id]->gebiet[$row->gebiet_id]->anz_antworten=$row->anz_antworten;
	$ergebnis[$row->pruefling_id]->gebiet[$row->gebiet_id]->anz_falsch=$row->anz_falsch;
	$ergebnis[$row->pruefling_id]->gebiet[$row->gebiet_id]->punkte=$row->punkte;
	$ergebnis[$row->pruefling_id]->gebiet[$row->gebiet_id]->prozent=$row->prozent;
	if (isset($ergebnis[$row->pruefling_id]->gesamt))
		$ergebnis[$row->pruefling_id]->gesamt+=$row->prozent;
	else
		$ergebnis[$row->pruefling_id]->gesamt=$row->prozent;
}
$ergb=sortByField($ergebnis,'gesamt');


// Vorkommende Kategorien laden
$sql_query="SELECT DISTINCT kategorie_kurzbz FROM testtool.vw_auswertung_kategorie";
//echo $sql_query;
if(!($result=pg_query($conn, $sql_query)))
    die(pg_errormessage($conn));
while ($row=pg_fetch_object($result))
	$kategorie[$row->kategorie_kurzbz]->name=$row->kategorie_kurzbz;

// Ergebnisse laden
$sql_query="SELECT vw_auswertung_kategorie.*, tbl_kriterien.typ FROM testtool.vw_auswertung_kategorie, testtool.tbl_kriterien
WHERE tbl_kriterien.kategorie_kurzbz=vw_auswertung_kategorie.kategorie_kurzbz
	AND vw_auswertung_kategorie.gebiet_id=tbl_kriterien.gebiet_id AND tbl_kriterien.punkte=vw_auswertung_kategorie.richtig ORDER BY pruefling_id, kategorie_kurzbz";
//echo $sql_query;
if(!($result=pg_query($conn, $sql_query)))
    die(pg_errormessage($conn));

while ($row=pg_fetch_object($result))
{
	$erg_kat[$row->pruefling_id]->pruefling_id=$row->pruefling_id;
	$erg_kat[$row->pruefling_id]->nachname=$row->nachname;
	$erg_kat[$row->pruefling_id]->vorname=$row->vorname;
	$erg_kat[$row->pruefling_id]->gebdatum=$row->gebdatum;
	$erg_kat[$row->pruefling_id]->geschlecht=$row->geschlecht;
	$erg_kat[$row->pruefling_id]->idnachweis=$row->idnachweis;
	$erg_kat[$row->pruefling_id]->registriert=$row->registriert;
	$erg_kat[$row->pruefling_id]->stg_kurzbz=$row->stg_kurzbz;
	$erg_kat[$row->pruefling_id]->stg_bez=$row->stg_bez;
	$erg_kat[$row->pruefling_id]->gruppe=$row->gruppe_kurzbz;
	$erg_kat[$row->pruefling_id]->kategorie[$row->kategorie_kurzbz]->name=$row->kategorie_kurzbz;
	$erg_kat[$row->pruefling_id]->kategorie[$row->kategorie_kurzbz]->richtig=$row->richtig;
	$erg_kat[$row->pruefling_id]->kategorie[$row->kategorie_kurzbz]->falsch=$row->falsch;
	$erg_kat[$row->pruefling_id]->kategorie[$row->kategorie_kurzbz]->gesamt=$row->gesamt;
	$erg_kat[$row->pruefling_id]->kategorie[$row->kategorie_kurzbz]->typ=$row->typ;
	$erg_kat[$row->pruefling_id]->kategorie[$row->kategorie_kurzbz]->punkte=$row->richtig.'/'.$row->gesamt;
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

<h1>Technischer Teil</h1>

<table id="zeitsperren">
  <tr>
		<th rowspan="2">ID</th><th rowspan="2">Nachname</th><th rowspan="2">Vornamen</th>
		<th rowspan="2">GebDatum</th><th rowspan="2">G</th><th rowspan="2">IdNachweis</th>
		<th rowspan="2">Registriert</th><th rowspan="2">STG</th><th rowspan="2">Studiengang</th>
		<th rowspan="2">Grp</th>
		<?php
		foreach ($gebiet AS $gbt)
			echo "<th colspan='2'>$gbt->name</th>";
		?>
		<th>Gesamt</th>
  </tr>
   <tr>
		<?php
		foreach ($gebiet AS $gbt)
			echo "<th><small>Punkte</small></th><th><small>Prozent</small></th>";
		?>
		<th><small>Prozentpunkte</small></th>
  </tr>
  <tr>
  	</th><th>
  </tr>
  <?php
  	foreach ($ergb AS $erg)
  	{
  		echo "<tr><td>$erg->pruefling_id</td><td>$erg->nachname</td><td>$erg->vorname</td><td>$erg->gebdatum</td><td>$erg->geschlecht</td>
  					<td>$erg->idnachweis</td><td>$erg->registriert</td><td>$erg->stg_kurzbz</td><td>$erg->stg_bez</td><td>$erg->gruppe</td>";
  		foreach ($gebiet AS $gbt)
  			if (isset($erg->gebiet[$gbt->gebiet_id]))
				echo '<td>'.$erg->gebiet[$gbt->gebiet_id]->punkte.'</td><td>'.$erg->gebiet[$gbt->gebiet_id]->prozent.' %</td>';
			else
				echo '<td></td><td></td>';
		echo '<td>'.$erg->gesamt.'</td>';
  		echo '</tr>';
  	}
  ?>
</table>

<h1>Kategorien</h1>

<table id="zeitsperren">
  <tr>
		<th rowspan="2">ID</th><th rowspan="2">Nachname</th><th rowspan="2">Vornamen</th>
		<th rowspan="2">GebDatum</th><th rowspan="2">G</th><th rowspan="2">IdNachweis</th>
		<th rowspan="2">Registriert</th><th rowspan="2">STG</th><th rowspan="2">Studiengang</th>
		<th rowspan="2">Grp</th>
		<?php
		foreach ($kategorie AS $gbt)
			echo "<th colspan='2'>$gbt->name</th>";
		?>
  </tr>
   <tr>
		<?php
		foreach ($kategorie AS $gbt)
			echo "<th><small>Punkte</small></th><th><small>Typ</small></th>";
		?>
  </tr>
  <tr>
  	</th><th>
  </tr>
  <?php
   	foreach ($erg_kat AS $erg)
  	{
  		echo "<tr><td>$erg->pruefling_id</td><td>$erg->nachname</td><td>$erg->vorname</td><td>$erg->gebdatum</td><td>$erg->geschlecht</td>
  					<td>$erg->idnachweis</td><td>$erg->registriert</td><td>$erg->stg_kurzbz</td><td>$erg->stg_bez</td><td>$erg->gruppe</td>";
  		foreach ($kategorie AS $gbt)
			echo '<td>'.$erg->kategorie[$gbt->name]->punkte.'</td><td>'.$erg->kategorie[$gbt->name]->typ.'</td>';
  		echo '</tr>';
  	}
  ?>
</table>


</body>
</html>
