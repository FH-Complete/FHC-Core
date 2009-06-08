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

if(!$conn=pg_pconnect(CONN_STRING))
   die("Konnte Verbindung zur Datenbank nicht herstellen");

$user = get_uid();

$rechte = new benutzerberechtigung($conn);
$rechte->getBerechtigungen($user);

$datum_obj = new datum();

if(isset($_GET['studiengang_kz']))
	$studiengang_kz = $_GET['studiengang_kz'];
else 
	$studiengang_kz = '';

echo '<html>
	<head>
		<title>PreInteressenten</title>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
		<link rel="stylesheet" href="../../include/js/tablesort/table.css" type="text/css">
		<script src="../../include/js/tablesort/table.js" type="text/javascript"></script>
	</head>
	<body class="Background_main">
	<h2>PreInteressenten</h2>
	';

$stg_admin = $rechte->getStgKz('admin');
$stg_assistenz = $rechte->getStgKz('assistenz');
$stgs = array_merge($stg_admin, $stg_assistenz);
sort($stgs);
//Wenn keine Berechtigung vorhanden ist beenden
if(count($stgs)==0)
	die('Sie haben keine Studiengangsberechtigung');

//alle Studiengaenge holen fuer die eine berechtigung vorhanden ist
$qry = "SELECT UPPER(typ::varchar(1) || kurzbz) as kuerzel, studiengang_kz FROM public.tbl_studiengang";

if($stgs[0]!=0)
{
	$stgwhere = '';
	foreach ($stgs as $stg)
	{
		if($stgwhere!='')
			$stgwhere.=',';
		$stgwhere .=$stg;
	}
	
	$qry.=" WHERE studiengang_kz in ($stgwhere)";
}
$qry.=" ORDER by kuerzel";

//Drop Down fuer Studiengaenge anzeigen
echo '<form action="'.$_SERVER['PHP_SELF'].'" method="GET">Studiengang: <select name="studiengang_kz">';
if($result = pg_query($conn, $qry))
{
	while($row = pg_fetch_object($result))
	{
		//wenn kein Studiengang uebergeben wurde dann den ersten nehmen fuer den eine Berechtigung vorhanden ist
		if($studiengang_kz=='')
			$studiengang_kz = $row->studiengang_kz;
		
		if($row->studiengang_kz == $studiengang_kz)
			$selected = 'selected';
		else 
			$selected = '';
		echo "<option value='$row->studiengang_kz' $selected>$row->kuerzel</option>";
	}
}
echo '</select><input type="submit" value="Anzeigen"></form>';

if(!$rechte->isBerechtigt('admin', $studiengang_kz, 'suid') && !$rechte->isBerechtigt('assistenz', $studiengang_kz, 'suid'))
	die('Sie haben keine Berechtigung fuer diese Seite');

if(isset($_POST['uebertragen']))
{
	$anzahl_fehler=0;
	$anzahl_uebernommen=0;
	foreach ($_POST as $param=>$val)
	{
		if(strstr($param, 'chk_'))
		{
			pg_query($conn, 'BEGIN;');
			
			$id = substr($param, 4);
			$preinteressent = new preinteressent($conn);
			if($preinteressent->load($id))
			{
				//Prestudent anlegen
				$prestudent = new prestudent($conn);
				$prestudent->new = true;
				$prestudent->aufmerksamdurch_kurzbz = $preinteressent->aufmerksamdurch_kurzbz;
				$prestudent->person_id = $preinteressent->person_id;
				$prestudent->studiengang_kz = $studiengang_kz;
				$prestudent->reihungstestangetreten = false;
				$prestudent->bismelden = true;
				$prestudent->insertamum = date('Y-m-d H:i:s');
				$prestudent->insertvon = $user;
				
				if($prestudent->save())
				{
					//Rolle anlegen	
					$prestudent->studiensemester_kurzbz = $preinteressent->studiensemester_kurzbz;
					
					//$preinteressent1 = new preinteressent($conn);
					//$preinteressent1->loadStudiengangszuteilung($preinteressent_id, $studiengang_kz);
					
					$prestudent->ausbildungssemester = 1;
					$prestudent->status_kurzbz = 'Interessent';
					$prestudent->datum = date('Y-m-d');
					$prestudent->insertamum = date('Y-m-d H:i:s');
					$prestudent->inservon = $user;
					
					if($prestudent->save_rolle(true))
					{
						//Uebernahme Datum setzen						 
						$qry = "UPDATE public.tbl_preinteressentstudiengang SET 
								uebernahmedatum='".date('Y-m-d H:i:s')."', 
								updateamum='".date('Y-m-d H:i:s')."', 
								updatevon='".$user."'
								WHERE studiengang_kz='$studiengang_kz' AND preinteressent_id='$id'";
						if(pg_query($conn, $qry))
						{
							$anzahl_uebernommen++;
							pg_query($conn, 'COMMIT');
						}
						else 
						{
							echo "<br>Fehler beim Eintragen des Uebernahmedatums";
							$anzahl_fehler++;
							pg_query($conn, 'ROLLBACK');
						}
					}
					else 
					{
						echo "<br>Fehler beim Anlegen der Rolle: $prestudent->errormsg";
						pg_query($conn, 'ROLLBACK');
						$anzahl_fehler++;
					}
				}
				else 
				{
					echo "<br>Fehler beim Speichern des Prestudenteintrages: $prestudent->errormsg";
					pg_query($conn, 'ROLLBACK');
					$anzahl_fehler++;
				}
			}
			else 
			{
				echo "<br>PreInteressent mit der ID $id konnte nicht geladen werden";
				pg_query($conn, 'ROLLBACK');
				$anzahl_fehler++;
			}
		}
	}
	echo "<br>Es wurde(n) <b>$anzahl_uebernommen Person(en) uebernommen</b>";
	if($anzahl_fehler>0)
		echo "<br>Es sind <b>$anzahl_fehler Fehler aufgetreten</b>";
}

if(isset($_GET['type']) && $_GET['type']=='zusammenlegung')
{
	if(isset($_GET['preinteressent_id']) && isset($_GET['personneu_id']))
	{
		$preinteressent_id = $_GET['preinteressent_id'];
		$person_id_neu = $_GET['personneu_id'];
		
		if(!is_numeric($preinteressent_id))
			die('Preinteressent_id ist ungueltig');
		if(!is_numeric($person_id_neu))
			die('person_id ist ungueltig');
		
		//Zusammenlegung
		//- Kontaktdaten werden zusammengelegt
		//- Personendatensatz des Preinteressenten wird verworfen		
		//- Uebernahmedatum wird gesetzt
		
		pg_query($conn, 'BEGIN;');
		
		$preinteressent=new preinteressent($conn);
		$preinteressent->load($preinteressent_id);
		
		$qry = "UPDATE public.tbl_kontakt SET person_id='$person_id_neu' WHERE person_id='$preinteressent->person_id';
				UPDATE public.tbl_adresse SET person_id='$person_id_neu' WHERE person_id='$preinteressent->person_id';
				UPDATE public.tbl_preinteressent SET person_id='$person_id_neu' WHERE preinteressent_id='$preinteressent_id';
				";
		
		if(!pg_query($conn, $qry))
		{
			pg_query($conn, 'ROLLBACK');
			die('Fehler beim Zusammenlegen der Kontaktdaten');
		}

		$qry = "UPDATE public.tbl_preinteressentstudiengang SET uebernahmedatum='".date('Y-m-d H:i:s')."',
				updateamum='".date('Y-m-d H:i:s')."', updatevon='$user'
				WHERE preinteressent_id='$preinteressent_id' AND studiengang_kz='".addslashes($studiengang_kz)."'";
		if(!pg_query($conn, $qry))
		{
			pg_query($conn, 'ROLLBACK');
			die('Fehler beim Setzen des Uebernahmedatums');
		}
		
		pg_query($conn, 'COMMIT');
		
		//Versuchen den Personendatensatz zu loeschen
		//(Falls die Person noch irgendwohin referenziert (Firmenbetreuer, Preinteressent,...)
		// wird das Loeschen von der DB verhindert, deshalb das @ vor dem pg_query)
		$qry = "DELETE FROM public.tbl_person WHERE person_id='$preinteressent->person_id'";
		@pg_query($conn, $qry);
	
		echo "<b>Personen wurden zusammengelegt</b>";
	}
	else 
		die('Preinteressent_id und personneu_id muss uebergeben werden');
}
echo '<br><br>';	
echo "<form action='".$_SERVER['PHP_SELF']."?studiengang_kz=$studiengang_kz' method='POST'>";
echo "<table class='liste table-autosort:0 table-stripeclass:alternate table-autostripe'>
	<thead>
		<tr>
		<th>&nbsp;</th>
		<th class='table-sortable:default'>Nachname</th>
		<th class='table-sortable:default'>Vorname</th>
		<th class='table-sortable:default'>GebDatum</th>
		<th class='table-sortable:default'>Studiensemester</th>
		<th class='table-sortable:default'>Anmerkung</th>
		<th class='table-sortable:default'>Zusammenlegung</th>
		</tr>
	</thead>
	<tbody>";
$preinteressent = new preinteressent($conn);
$preinteressent->loadFreigegebene($studiengang_kz);

foreach ($preinteressent->result as $row)
{
	echo '<tr>';
	$person = new person($conn);
	$person->load($row->person_id);
	echo "<td><input type='checkbox' name='chk_$row->preinteressent_id' checked></td>";
	echo "<td>$person->nachname</td>";
	echo "<td>$person->vorname</td>";
	echo "<td>$person->gebdatum</td>";
	echo "<td>$row->studiensemester_kurzbz</td>";
	echo "<td>$row->anmerkung</td>";
	echo "<td>";
	
	//Suchen ob diese Person schon existiert
	$qry = "SELECT distinct * FROM public.tbl_prestudent JOIN public.tbl_person USING(person_id) WHERE 
				studiengang_kz='$studiengang_kz' AND (
				(vorname='$person->vorname' AND nachname='$person->nachname') ";
	if($person->gebdatum!='')
		$qry.=" OR (nachname='$person->nachname' AND gebdatum='$person->gebdatum')";
	$qry.=")";
	if($result_double = pg_query($conn, $qry))
	{
		if(pg_num_rows($result_double)>0)
		{
			//wenn zu dieser Person bereits ein Prestudent oder Benutzer existiert,
			//dann kann die zusammenlegung nur ueber die administration erfolgen
			//(damit wird verhindert, dass unbeabsichtigt verschiedene Personen zusammengelegt werden)
			$qry = "SELECT prestudent_id FROM public.tbl_prestudent WHERE person_id='$row->person_id'
					UNION 
					SELECT person_id FROM public.tbl_benutzer WHERE person_id='$row->person_id'
					";
			if($result_anz = pg_query($conn, $qry))
			{
				if(pg_num_rows($result_anz)==0)
				{	
					echo '<SELECT name="person_id" id="person_id_'.$row->preinteressent_id.'">';
					while($row_double=pg_fetch_object($result_double))
					{
						echo "<OPTION value='$row_double->person_id'>$row_double->nachname $row_double->vorname $row_double->gebdatum ($row_double->person_id)</OPTION>";
					}
					echo '</SELECT>';
					
					echo '<INPUT type="button" value="Zusammenlegen" onclick="window.location.href= \''.$_SERVER['PHP_SELF'].'?type=zusammenlegung&studiengang_kz='.$studiengang_kz.'&preinteressent_id='.$row->preinteressent_id.'&personneu_id=\'+document.getElementById(\'person_id_'.$row->preinteressent_id.'\').value;">';
				}
				else 
				{
					echo 'nur durch Administrator möglich';
				}
			}
		}
	}		
	
	echo "</td>";
	echo '</tr>';
}
echo '</tbody></table><br>';
echo '<input type="submit" value="Uebertragen" name="uebertragen"></form>';
?>