<?php
/* Copyright (C) 2009 Technikum-Wien
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307, USA.
 *
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>,
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>,
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at> and
 *          Gerald Simane-Sequens <gerald.simane-sequens@technikum-wien.at>.
 */

require_once('../../config.inc.php');
require_once('../../../include/functions.inc.php');
require_once('../../../include/studiengang.class.php');

function sortByField($multArray,$sortField,$desc=true)
{
	$tmpKey='';
    $ResArray=array();

    if(!is_array($multArray))
    	return array();
    
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

$ergebnis='';
$gebiet=array();
$kategorie=array();
$erg_kat=array();

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
	$sql_query="SELECT DISTINCT gebiet_id, gebiet FROM testtool.vw_auswertung WHERE reihungstest_id='".addslashes($_POST['reihungstest'])."'";
	if(isset($_POST['studiengang']) && $_POST['studiengang']!='')
		$sql_query.=" AND tbl_prestudent.studiengang_kz='".addslashes($_POST['studiengang'])."'";
	
	//echo $sql_query;
	if(!($result=pg_query($conn, $sql_query)))
	    die(pg_errormessage($conn));
	while ($row=pg_fetch_object($result))
	{
		$gebiet[$row->gebiet_id]->name=$row->gebiet;
		$gebiet[$row->gebiet_id]->gebiet_id=$row->gebiet_id;
	}
	
	// Alle Personen des Reihungstests laden
	$sql_query="SELECT 
					*
				FROM
					testtool.vw_auswertung
				WHERE
					reihungstest_id='".addslashes($_POST['reihungstest'])."'";
	if(isset($_POST['studiengang']) && $_POST['studiengang']!='')
		$sql_query.=" AND tbl_prestudent.studiengang_kz='".addslashes($_POST['studiengang'])."'";
	
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
		$ergebnis[$row->pruefling_id]->semester=$row->semester;
				
		$ergebnis[$row->pruefling_id]->gebiet[$row->gebiet_id]->name=$row->gebiet;
		$ergebnis[$row->pruefling_id]->gebiet[$row->gebiet_id]->punkte=(($row->punkte>=$row->maxpunkte)?$row->maxpunkte:$row->punkte);
		//wenn maxpunkte ueberschritten wurde -> 100%
		if($row->punkte>=$row->maxpunkte)
			$prozent=100;
		else
			$prozent = ($row->punkte/$row->maxpunkte)*100;
		$ergebnis[$row->pruefling_id]->gebiet[$row->gebiet_id]->prozent=$prozent;
		
		if (isset($ergebnis[$row->pruefling_id]->gesamt))
			$ergebnis[$row->pruefling_id]->gesamt+=$prozent*$row->gewicht;
		else
			$ergebnis[$row->pruefling_id]->gesamt=$prozent*$row->gewicht;
	}
	
	$ergb=sortByField($ergebnis,'gesamt');
	
	// Vorkommende Kategorien laden
	$sql_query="SELECT 
					DISTINCT kategorie_kurzbz, 
					(SELECT sum(punkte) FROM testtool.tbl_vorschlag JOIN testtool.tbl_frage USING(frage_id) 
					 WHERE tbl_frage.kategorie_kurzbz=vw_auswertung_kategorie.kategorie_kurzbz) as gesamtpunkte 
				 FROM testtool.vw_auswertung_kategorie
				 WHERE reihungstest_id='".addslashes($_POST['reihungstest'])."'";

	if(isset($_POST['studiengang']) && $_POST['studiengang']!='')
		$sql_query.=" AND tbl_prestudent.studiengang_kz='".$_POST['studiengang']."'";
	
	//echo $sql_query;
	if(!($result=pg_query($conn, $sql_query)))
	    die(pg_errormessage($conn));
	$gesamtpunkte=array();
	
	while ($row=pg_fetch_object($result))
	{
		$gesamtpunkte[$row->kategorie_kurzbz]=$row->gesamtpunkte;
		$kategorie[$row->kategorie_kurzbz]->name=$row->kategorie_kurzbz;
	}
	
	// Ergebnisse laden
	$sql_query="
		SELECT 
			vw_auswertung_kategorie.*, 
			(SELECT typ FROM testtool.tbl_kriterien 
			 WHERE gebiet_id=vw_auswertung_kategorie.gebiet_id AND punkte=vw_auswertung_kategorie.punkte 
			 AND kategorie_kurzbz=vw_auswertung_kategorie.kategorie_kurzbz) as typ
		FROM testtool.vw_auswertung_kategorie
		WHERE reihungstest_id='".addslashes($_POST['reihungstest'])."'";
	if(isset($_POST['studiengang']) && $_POST['studiengang']!='')
		$sql_query.=" AND studiengang_kz='".$_POST['studiengang']."'";
	
	$sql_query.=" ORDER BY nachname, vorname";
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
		$erg_kat[$row->pruefling_id]->kategorie[$row->kategorie_kurzbz]->name=$row->kategorie_kurzbz;
		$erg_kat[$row->pruefling_id]->kategorie[$row->kategorie_kurzbz]->typ=$row->typ;
		$erg_kat[$row->pruefling_id]->kategorie[$row->kategorie_kurzbz]->punkte=number_format($row->punkte,2).'/'.number_format($gesamtpunkte[$row->kategorie_kurzbz],2);
	}
}

//Studiengaenge laden
$stg_obj = new studiengang($conn);
$stg_obj->getAll(null, false);
$stg_arr = array();

foreach($stg_obj->result as $row)
	$stg_arr[$row->studiengang_kz]=$row->kuerzel;

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>

<head>
	<title>Testtool - Auswertung</title>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-15">
	<link rel="stylesheet" href="../../../skin/style.css.php">
</head>

<body>

<h1>Auswertung Reihungstest</h1>
<form method="POST">
Reihungstest w&auml;hlen:&nbsp;
	<SELECT name="reihungstest">';
		<?php
		foreach($rtest as $rt)
		{
			if(isset($_POST['reihungstest']) && $rt->reihungstest_id==$_POST['reihungstest'])
				$selected = 'selected';
			else 
				$selected = '';
				
			echo '<OPTION value="'.$rt->reihungstest_id.'" '.$selected.'>'.(isset($stg_arr[$rt->studiengang_kz])?$stg_arr[$rt->studiengang_kz]:'').' '.$rt->ort_kurzbz.' '.$rt->anmerkung.' '.$rt->datum."</OPTION>\n";
		}
		?>
	</SELECT>
Studiengang:
	<SELECT name="studiengang">
		<OPTION value=''>Alle</OPTION>
		<?php
		foreach ($stg_arr as $kz=>$kurzbz)
		{
			if(isset($_POST['studiengang']) && $_POST['studiengang']==$kz && $_POST['studiengang']!='')
				$selected='selected';
			else 
				$selected='';
			
			echo '<OPTION value="'.$kz.'" '.$selected.'>'.$kurzbz.'</OPTION>';
		}
		?>
	</SELECT>
	<INPUT type="submit" value="Auswerten" />
</form>

<?php
if (isset($_POST['reihungstest']))
{
	?>
<h1>Technischer Teil</h1>

<table id="zeitsperren">
  <tr>
		<th rowspan="2">ID</th><th rowspan="2">Nachname</th><th rowspan="2">Vornamen</th>
		<th rowspan="2">GebDatum</th><th rowspan="2">G</th>
		<!--<th rowspan="2">IdNachweis</th>-->
		<th rowspan="2">Registriert</th><th rowspan="2">STG</th><th rowspan="2">Studiengang</th>
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
  <?php
  if(isset($ergb))
  {
  	foreach ($ergb AS $erg)
  	{
  		echo "<tr><td>$erg->pruefling_id</td><td>$erg->nachname</td><td>$erg->vorname</td><td>$erg->gebdatum</td><td>$erg->geschlecht</td>
  					<td>$erg->registriert</td><td>$erg->stg_kurzbz</td><td>$erg->stg_bez</td>";
  		//<td>$erg->idnachweis</td>
  		foreach ($gebiet AS $gbt)
  			if (isset($erg->gebiet[$gbt->gebiet_id]))
				echo '<td>'.number_format($erg->gebiet[$gbt->gebiet_id]->punkte,2,',',' ').'</td><td nowrap>'.number_format($erg->gebiet[$gbt->gebiet_id]->prozent,2,',',' ').' %</td>';
			else
				echo '<td></td><td></td>';
		echo '<td>'.number_format($erg->gesamt,2,',',' ').'</td>';
  		echo '</tr>';
  	}
  }
  ?>
</table>

<h1>Kategorien</h1>

<table id="zeitsperren">
  <tr>
		<th rowspan="2">ID</th><th rowspan="2">Nachname</th><th rowspan="2">Vornamen</th>
		<th rowspan="2">GebDatum</th><th rowspan="2">G</th>
		<!--<th rowspan="2">IdNachweis</th>-->
		<th rowspan="2">Registriert</th><th rowspan="2">STG</th><th rowspan="2">Studiengang</th>
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
  <?php
   	foreach ($erg_kat AS $erg)
  	{
  		echo "<tr><td>$erg->pruefling_id</td><td>$erg->nachname</td><td>$erg->vorname</td><td>$erg->gebdatum</td><td>$erg->geschlecht</td>
  					<td>$erg->registriert</td><td>$erg->stg_kurzbz</td><td>$erg->stg_bez</td>";
  		//<td>$erg->idnachweis</td>
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
