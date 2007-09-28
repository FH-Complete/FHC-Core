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
if (!$conn = pg_connect(CONN_STRING))
	die("Es konnte keine Verbindung zum Server aufgebaut werden.");

// Reihungstests laden
$sql_query=";
		SELECT * FROM public.tbl_reihungstest WHERE date_part('year',datum)=date_part('year',now()) ORDER BY datum,uhrzeit";
//echo $sql_query;
if(!($result=pg_query($conn, $sql_query)))
    die(pg_errormessage($conn));
while ($row=pg_fetch_object($result))
{
	$rtest[$row->reihungstest_id]->reihungstest_id=$row->reihungstest_id;
	$rtest[$row->reihungstest_id]->studiengang_kz=$row->studiengang_kz;
	$rtest[$row->reihungstest_id]->ort_kurzbz=$row->ort_kurzbz;
	$rtest[$row->reihungstest_id]->anmerkung=$row->anmerkung;
	$rtest[$row->reihungstest_id]->datum=$row->datum;
	$rtest[$row->reihungstest_id]->uhrzeit=$row->uhrzeit;
}

if (isset($_POST['reihungstest']))
{
// Vorkommende Gebiete laden
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
$sql_query="SELECT vw_auswertung.* FROM testtool.vw_auswertung";
if (isset($_POST['reihungstest']))
	$sql_query.=' JOIN public.tbl_prestudent USING (prestudent_id) WHERE TRUE OR reihungstest_id='.$_POST['reihungstest'];

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
if (isset($_POST['reihungstest']))
	$sql_query.=' JOIN public.tbl_prestudent USING (prestudent_id) WHERE reihungstest_id='.$_POST['reihungstest'];
//echo $sql_query;
if(!($result=pg_query($conn, $sql_query)))
    die(pg_errormessage($conn));
while ($row=pg_fetch_object($result))
	$kategorie[$row->kategorie_kurzbz]->name=$row->kategorie_kurzbz;

// Ergebnisse laden
$sql_query="SELECT vw_auswertung_kategorie.*, tbl_kriterien.typ
	FROM (testtool.vw_auswertung_kategorie JOIN testtool.tbl_kriterien USING (kategorie_kurzbz))";
if (isset($_POST['reihungstest']))
	$sql_query.=' JOIN public.tbl_prestudent USING (prestudent_id)';
$sql_query.=" WHERE vw_auswertung_kategorie.gebiet_id=tbl_kriterien.gebiet_id AND tbl_kriterien.punkte=vw_auswertung_kategorie.richtig";
if (isset($_POST['reihungstest']))
	$sql_query.=' AND reihungstest_id='.$_POST['reihungstest'];

$sql_query.=" ORDER BY pruefling_id, kategorie_kurzbz";
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

<h1>Auswertung Reihungstest</h1>
Reihungstest w&auml;hlen:&nbsp;
<form method="POST">
	<SELECT name="reihungstest">';
		<?php
		foreach($rtest as $rt)
				echo '<OPTION value="'.$rt->reihungstest_id.'">'.$rt->studiengang_kz.' '.$rt->ort_kurzbz.' '.$rt->anmerkung.' '.$rt->datum.' '.$prestd->rt."</OPTION>\n";
		?>
	</SELECT>
	<INPUT type="submit" value="select" />
</form>

<?php
if (isset($_POST['reihungstest']))
{
	?>
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
				echo '<td>'.number_format($erg->gebiet[$gbt->gebiet_id]->punkte,2,',',' ').'</td><td nowrap>'.number_format($erg->gebiet[$gbt->gebiet_id]->prozent,2,',',' ').' %</td>';
			else
				echo '<td></td><td></td>';
		echo '<td>'.number_format($erg->gesamt,2,',',' ').'</td>';
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

<?php
}
?>
</body>
</html>
