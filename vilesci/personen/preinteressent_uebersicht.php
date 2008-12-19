<?php
/* Copyright (C) 2007 Technikum-Wien
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
 *          Gerald Raab <gerald.raab@technikum-wien.at>.
 */
require_once('../config.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/benutzerberechtigung.class.php');
require_once('../../include/studiengang.class.php');
require_once('../../include/preinteressent.class.php');
require_once('../../include/person.class.php');
require_once('../../include/datum.class.php');
require_once('../../include/prestudent.class.php');
require_once('../../include/studiensemester.class.php');
require_once('../../include/log.class.php');
require_once('../../include/mail.class.php');

if(!$conn=pg_pconnect(CONN_STRING))
   die("Konnte Verbindung zur Datenbank nicht herstellen");

$user = get_uid();

$rechte = new benutzerberechtigung($conn);
$rechte->getBerechtigungen($user);

$datum_obj = new datum();
$stsem = new studiensemester($conn);
$stsem_aktuell = $stsem->getaktorNext();

if(isset($_GET['studiengang_kz']))
	$studiengang_kz = $_GET['studiengang_kz'];
else 
	$studiengang_kz = '';

if(isset($_GET['studiensemester_kurzbz']))
	$studiensemester_kurzbz = $_GET['studiensemester_kurzbz'];
else 
	$studiensemester_kurzbz = '-1'; //$stsem_aktuell;

if(isset($_GET['bool_nichtfreigegeben']))
	$bool_nichtfreigegeben = true;
else 
	$bool_nichtfreigegeben = null;
	
if(isset($_GET['bool_uebernommen']))
	$bool_uebernommen = true;
else 
	$bool_uebernommen = null;

if(isset($_GET['bool_absage']))
	$bool_absage = true;
else 
	$bool_absage = false;

if(isset($_GET['filter']))
	$filter = $_GET['filter'];
else 
	$filter = '';
	
//Wenn auf Anzeigen geklickt wird, das Suchfeld nicht beruecksichtigen
if(isset($_GET['anzeigen']))
	$filter='';

if(isset($_GET['kontaktmedium']))
{
	$kontaktmedium = $_GET['kontaktmedium'];
	if($kontaktmedium=='')
		$kontaktmedium=null;
}
else 
	$kontaktmedium = null;
if(isset($_GET['erfassungsdatum_bis']) && $_GET['erfassungsdatum_bis']!='')
	$erfassungsdatum_bis = $_GET['erfassungsdatum_bis'];
else
	$erfassungsdatum_bis=null;
	
if(isset($_GET['erfassungsdatum_von']) && $_GET['erfassungsdatum_von']!='')
	$erfassungsdatum_von = $_GET['erfassungsdatum_von'];
else
	$erfassungsdatum_von=null;
	
//Doctype muss strict sein da sonst im IE der DIV nicht am oberen Rand fixiert ist
echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
        "http://www.w3.org/TR/html4/strict.dtd">
<html>
	<head>
		<title>PreInteressenten</title>
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-15">
		<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
		<link rel="stylesheet" href="../../include/js/tablesort/table.css" type="text/css">
		<script src="../../include/js/tablesort/table.js" type="text/javascript"></script>
		<script language="Javascript">
		<!--
		function confdel()
		{
			if(confirm("Wollen Sie diesen Eintrag wirklich loeschen?"))
				return true;
			else
				return false;
		}
		
		function sorttable()
		{
			//Meister IE braucht ein Timeout sonst sortiert er nicht
			window.setTimeout("Table.sort(document.getElementById(\'mytab\'),\'asc\')",10);
		}
		-->
		</script>
	</head>
	<body class="Background_main" style="margin-top:0px; padding-top:0px;" onload="sorttable()" >
	<div style="position: fixed; background-color: white; width: 99%; padding-top:5px;">
	<h2>PreInteressenten</h2>
	';

if(!$rechte->isBerechtigt('admin', null, 'suid') && 
   !$rechte->isBerechtigt('preinteressent', null, 'suid'))
	die('Sie haben keine Berechtigung fuer diese Seite');

//DROP DOWNs anzeigen
echo "<table width='100%'><tr><td><form action='".$_SERVER['PHP_SELF']."' method='GET'>";
echo '<table><tr><td>Studiensemester: <SELECT name="studiensemester_kurzbz">';
$stsem = new studiensemester($conn);
$stsem->getAll();
echo "<option value='-1' ".($studiensemester_kurzbz=='-1'?'selected':'').">-- alle --</option>";
echo "<option value='' ".($studiensemester_kurzbz==''?'selected':'').">-- offen --</option>";
foreach ($stsem->studiensemester as $row)	
{
	if($row->studiensemester_kurzbz==$studiensemester_kurzbz)
		$selected='selected';
	else 
		$selected='';
	
	echo "<option value='$row->studiensemester_kurzbz' $selected>$row->studiensemester_kurzbz</option>";
}
echo '</SELECT>';

echo '&nbsp;&nbsp;&nbsp;Studiengang: <SELECT name="studiengang_kz">';
echo "<option value=''>-- Alle --</option>";
$stg = new studiengang($conn);
$stg->getAll('typ, kurzbz');
foreach ($stg->result as $row)
{
	if($row->studiengang_kz==$studiengang_kz)
		$selected='selected';
	else 
		$selected='';
		
	echo "<option value='$row->studiengang_kz' $selected>$row->kuerzel</option>";
}
echo '</SELECT></td><td>';
echo '<input type="checkbox" name="bool_nichtfreigegeben" '.($bool_nichtfreigegeben?'checked':'').'> nicht freigegeben';
echo '<input type="checkbox" name="bool_absage" '.($bool_absage?'checked':'').'> Absage<br>';
echo '<input type="checkbox" name="bool_uebernommen" '.($bool_uebernommen?'checked':'').'> freigegeben aber nicht &uuml;bernommen</td><td>';
echo '&nbsp;&nbsp;&nbsp;<input type="submit" name="anzeigen" value="Anzeigen"></td></tr>';
echo '<tr><td>Kontaktmedium: <SELECT name="kontaktmedium">';
$qry="SELECT * FROM public.tbl_kontaktmedium ORDER BY beschreibung";
echo "<option value='' >-- Alle --</option>";
if($result = pg_query($conn, $qry))
{
	while($row = pg_fetch_object($result))
	{
		if($row->kontaktmedium_kurzbz==$kontaktmedium)
			$selected='selected';
		else 
			$selected='';
		echo "<option value='$row->kontaktmedium_kurzbz' $selected>$row->beschreibung</option>";	
	}
}
echo '</SELECT></td><td>';
echo 'Erf. von <input type="text" size="10" maxlength="10" name="erfassungsdatum_von" value="'.$erfassungsdatum_von.'">';
echo 'Erf. bis <input type="text" size="10" maxlength="10" name="erfassungsdatum_bis" value="'.$erfassungsdatum_bis.'">';
echo '</td></tr>';
echo '</table>';
//echo '</form>';
echo '</td><td>';
//echo "<form action='".$_SERVER['PHP_SELF']."' method='GET'>";
echo "<input type='text' value='".htmlentities($filter,ENT_QUOTES)."' name='filter'>&nbsp;";
echo "<input type='submit' size='10' name='suchen' value='Suchen'>";
echo '</form></td>';
echo '<td align="right"><a href="preinteressent_anlegen.php" target="_blank">neuen Preinteressenten anlegen</a></td></tr></table>';

//FREIGEBEN / LOESCHEN
if(isset($_GET['action']))
{
	if($_GET['action']=='freigabe')
	{
		$preinteressent = new preinteressent($conn);
		$preinteressent->load($_GET['id']);
		
		if($preinteressent->studiensemester_kurzbz!='')
		{
			$errormsg = '';
			$anzahl_freigegeben=0;
			$anzahl_fehler=0;
			$qry = "SELECT * FROM public.tbl_preinteressentstudiengang 
					WHERE preinteressent_id='".addslashes($_GET['id'])."' 
						  AND prioritaet = (SELECT max(prioritaet) 
						  					FROM public.tbl_preinteressentstudiengang 
						  					WHERE preinteressent_id='".addslashes($_GET['id'])."')
						  AND freigabedatum is null";
			//Zuordnungen holen die noch nicht freigegeben wurden und die hoechste Prioritaet haben
			if($result = pg_query($conn, $qry))
			{
				while($row = pg_fetch_object($result))
				{
					//Nur diejenigen nehmen die noch nicht als Prestudent vorhanden sind
					$qry = "SELECT count(*) as anzahl FROM public.tbl_preinteressent JOIN public.tbl_prestudent USING(person_id) WHERE preinteressent_id='$row->preinteressent_id' AND studiengang_kz='$row->studiengang_kz'";
					if($result_std = pg_query($conn, $qry))
					{
						if($row_std = pg_fetch_object($result_std))
						{
							if($row_std->anzahl==0)
							{
								$preinteressent = new preinteressent($conn);
								$preinteressent->loadZuordnung($row->preinteressent_id, $row->studiengang_kz);
								
								$preinteressent->freigabedatum = date('Y-m-d H:i:s');
								$preinteressent->updateamum = date('Y-m-d H:i:s');
								$preinteressent->updatevon = $user;
								
								if($preinteressent->saveZuordnung(false))
								{
									//MAIL an Assistenz verschicken
									$qry_person = "SELECT vorname, nachname 
													FROM public.tbl_person JOIN public.tbl_preinteressent USING(person_id) 
													WHERE preinteressent_id='$row->preinteressent_id'";
									$name='';
									if($result_person = pg_query($conn, $qry_person))
										if($row_person = pg_fetch_object($result_person))
											$name = $row_person->nachname.' '.$row_person->vorname;
									$stg_obj = new studiengang($conn);
									$stg_obj->load($row->studiengang_kz);
									$to = $stg_obj->email;
									//$to = 'oesi@technikum-wien.at';
									$message = "Dies ist eine automatische Mail! $stg_obj->email\n\n".
												"Der Preinteressent $name wurde zur �bernahme freigegeben. \nSie k�nnen diesen ".
												"im FAS unter 'Extras->Preinteressenten �bernehmen' oder unter folgendem Link\n\n".
												APP_ROOT."vilesci/personen/preinteressent_uebernahme.php?studiengang_kz=$row->studiengang_kz \n".
												"ins FAS �bertragen";
									$mail = new mail($to, 'vilesci@'.DOMAIN, 'Preinteressent Freigabe', $message);
									if($mail->send())
										echo "<br><b>Freigabemail wurde an $to versendet</b>";
									else 
										echo "<br><b>Fehler beim Versenden des Freigabemails an $to</b>";
									
									$anzahl_freigegeben++;
								}
								else 
								{
									$anzahl_fehler++;
									$errormsg.="<br>Fehler bei der Freigabe von ".$studiengang->kuerzel_arr[$row->studiengang_kz].": $preinteressent->errormsg";
								}
							}
						}
					}
				}
			}
			echo "<br><b>Es wurden $anzahl_freigegeben Studieng�nge freigegeben<br>";
			echo "<script language='Javascript'>
					parent.preinteressent_detail.location.href = \"preinteressent_detail.php?id=".$_GET['id']."&selection=\"+parent.preinteressent_detail.selection; 
				 </script>";
			if($anzahl_fehler>0)
				echo "Es sind $anzahl_fehler Fehler aufgetreten: $errormsg";
			echo '</b>';
		}
		else 
		{
			echo '<b>Es k�nnen nur Preinteressenten freigegeben werden, bei denen ein Studiensemester angegeben wurde</b>';
		}
	}
	elseif($_GET['action']=='loeschen')
	{
		//Loeschen eines Preinteressenten
		$preinteressent = new preinteressent($conn);
		if($preinteressent->load($_GET['id']))
		{
			if($preinteressent->delete($preinteressent->preinteressent_id))
			{
				echo '<br><b>Datensatz wurde geloescht</b>';
			}
			else 
			{
				echo "<br><b>Fehler beim L�schen: $preinteressent->errormsg</b>";
			}
		}
		else 
		{
			echo "<br><b>Fehler beim Laden des Datensatzes. Daten wurden NICHT gel�scht</b>";
		}
	}
	
}

//Datum pruefen
if($erfassungsdatum_bis!='' && !$datum_obj->formatDatum($erfassungsdatum_bis))
	die('Erf.bis Datum ist ungueltig');
if($erfassungsdatum_von!='' && !$datum_obj->formatDatum($erfassungsdatum_von))
	die('Erf.von Datum ist ungueltig');	

$preinteressent = new preinteressent($conn);
//if($filter=='')
if($datum_obj->formatDatum($filter, 'Y-m-d', true))
	$filter = $datum_obj->formatDatum($filter, 'Y-m-d', true);
$preinteressent->loadPreinteressenten($studiengang_kz, ($studiensemester_kurzbz!='-1'?$studiensemester_kurzbz:null), $filter, $bool_nichtfreigegeben, $bool_uebernommen, $kontaktmedium, $bool_absage, $erfassungsdatum_von, $erfassungsdatum_bis);
/*else 
{
	//Falls im Filter-Feld ein Datum steht dann wird dieses umformatiert
	if($datum_obj->formatDatum($filter, 'Y-m-d'))
		$filter = $datum_obj->formatDatum($filter, 'Y-m-d');
	$preinteressent->loadPreinteressenten(null, null, $filter);
}*/
$stg_obj = new studiengang($conn);
$stg_obj->getAll('typ, kurzbz', false);

function CutString($strVal, $limit)
{
	if(strlen($strVal) > $limit+3)
	{
		return substr($strVal, 0, $limit) . "...";
	}
	else
	{
		return $strVal;
	}
}
echo 'Anzahl: '.count($preinteressent->result);
echo '</div>'; // Fixiertes Div mit den Filtern
echo '<br><br><br><br><br><br><br>';

//TABELLE ANZEIGEN
echo '<br>';	
echo "<table id='mytab' class='liste table-autosort:4 table-stripeclass:alternate table-autostripe' style='font-size:15px;'>
	<thead>
		<tr>
		<th class='table-sortable:numeric'>ID</th>
		<th class='table-sortable:default'>Nachname</th>
		<th class='table-sortable:default'>Vorname</th>
		<th class='table-sortable:default'>StSem</th>
		<th class='table-sortable:default'>Erf.datum</th>
		<th class='table-sortable:default'>G</th>
		<th class='table-sortable:default'>E-Mail</th>
		<th class='table-sortable:default'>Status</th>
		<th class='table-sortable:default'>Freigabe</th>
		<th class='table-sortable:default'>&Uuml;bernahme</th>
		<th class='table-sortable:default'>Anmerkung</th>
		<th>Aktion</th>
		</tr>
	</thead>
	<tbody>";

foreach ($preinteressent->result as $row)
{
	echo '<tr>';
	$person = new person($conn);
	$person->load($row->person_id);
	echo "<td>$person->person_id</td>";
	echo "<td>$person->nachname</td>";
	echo "<td>$person->vorname</td>";
	//echo "<td>".$datum_obj->convertISODate($person->gebdatum)."</td>";
	echo "<td>$row->studiensemester_kurzbz</td>";
	echo "<td><span style='display: none'>$row->erfassungsdatum</span>".$datum_obj->formatDatum($row->erfassungsdatum,'d.m.Y')."</td>";
	
	echo "<td>$person->geschlecht</td>";
	//EMail
	$qry = "SELECT kontakt FROM public.tbl_kontakt WHERE person_id='$person->person_id' AND kontakttyp='email' 
			ORDER BY zustellung DESC LIMIT 1";
	echo '<td>';
	if($result_mail = pg_query($conn, $qry))
	{
		if($row_mail = pg_fetch_object($result_mail))
		{
			echo '<a href="mailto:'.$row_mail->kontakt.'" class="Item">'.$row_mail->kontakt.'</a>';
		}
	}
	echo '</td>';
	//Status
	$status='';
	$prestudent = new prestudent($conn);
	if($prestudent->getPrestudenten($row->person_id))
	{
		foreach ($prestudent->result as $prestd)
		{
			if($status!='')
				$status.=', ';
			$prestudent1 = new prestudent($conn);
			$prestudent1->getLastStatus($prestd->prestudent_id);
			$status.= $prestudent1->rolle_kurzbz.' ('.$stg_obj->kuerzel_arr[$prestd->studiengang_kz].')';
		}
	}
	if($status=='')
		$status='Preinteressent';
	echo "<td>$status</td>";
	
	//Zuordnungen laden und freigegebene Eintraege farblich markieren
	$freigaben = new preinteressent($conn);
	$freigaben->loadZuordnungen($row->preinteressent_id);
	$freigabe='';
	$uebernahme='';
	foreach ($freigaben->result as $row_freigaben)
	{
		//auch jene als freigegeben anzeigen die schon im studiengang angelegt sind 
		//obwohl der preinteressent nicht freigegeben wurde. (bewerbung direkt beim studiengang)
		$qry = "SELECT prestudent_id FROM public.tbl_prestudent WHERE person_id='$row->person_id' AND studiengang_kz='$row_freigaben->studiengang_kz'";
		$result_chkstg = pg_query($conn, $qry);
		
		if($row_freigaben->freigabedatum!='' || pg_num_rows($result_chkstg)>0)
			$freigabe.="<font color='#009900'>";
		else 
			$freigabe.="<font color='#FF0000'>";
		$freigabe.=$stg_obj->kuerzel_arr[$row_freigaben->studiengang_kz]."($row_freigaben->prioritaet)";
		$freigabe.='</font> ';
		
		if($row_freigaben->freigabedatum!='')
		{
			if($row_freigaben->uebernahmedatum!='')
				$uebernahme.="<font color='#009900'>";
			else 
				$uebernahme.="<font color='#FF0000'>";
			$uebernahme.=$stg_obj->kuerzel_arr[$row_freigaben->studiengang_kz];
			$uebernahme.='</font> ';
		}
	}
	
	echo "<td>$freigabe</td>";
	echo "<td>$uebernahme</td>";
	echo "<td title='".htmlentities($row->anmerkung,ENT_QUOTES)."'>".htmlentities(CutString($row->anmerkung, 20),ENT_QUOTES)."</td>";
	echo '<td>';
	echo " <input style='padding:0px;' type='button' onclick=\"window.open('personendetails.php?id=$row->person_id','_blank')\" value='Gesamt�bersicht' title='Zeigt die Details dieser Person an'>";
	echo " <input style='padding:0px;' type='button' onclick='parent.preinteressent_detail.location.href = \"preinteressent_detail.php?id=$row->preinteressent_id&selection=\"+parent.preinteressent_detail.selection; return false;' value='Bearbeiten' title='Zeigt die Details dieser Person an'>";
	echo " <input style='padding:0px;' type='button' onclick=\"window.location.href='".$_SERVER['PHP_SELF']."?id=$row->preinteressent_id&action=freigabe&studiensemester_kurzbz=$studiensemester_kurzbz&studiengang_kz=$studiengang_kz&filter=$filter'\" value='Freigeben' title='Gibt alle Studieng�nge mit der h�chsten Priorit�t frei'>";
	echo " <input style='padding:0px;' type='button' onclick=\"if(confdel()) {window.location.href='".$_SERVER['PHP_SELF']."?id=$row->preinteressent_id&action=loeschen&studiensemester_kurzbz=$studiensemester_kurzbz&studiengang_kz=$studiengang_kz&filter=$filter'}\" value='L�schen' title='L�scht diesen Preinteressenten'>";
	echo '</td>';
	echo '</tr>';
}
echo '</tbody></table><br>';

echo '</body>';
echo '</html>';
?>