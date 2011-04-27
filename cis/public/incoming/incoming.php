<?php
/* Copyright (C) 2010 Technikum-Wien
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
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *          Karl Burkhart <burkhart@technikum-wien.at>.
 */
 

require_once '../../../config/cis.config.inc.php';
require_once 'auth.php';
require_once '../../../include/mobilitaetsprogramm.class.php';
require_once '../../../include/person.class.php'; 
require_once '../../../include/functions.inc.php';
require_once '../../../include/phrasen.class.php';
require_once '../../../include/preincoming.class.php';
require_once '../../../include/nation.class.php'; 
require_once '../../../include/adresse.class.php';
require_once '../../../include/kontakt.class.php';
require_once '../../../include/studiensemester.class.php';
require_once '../../../include/studiengang.class.php';
require_once '../../../include/lehrveranstaltung.class.php';
require_once '../../../include/zweck.class.php';

if(isset($_GET['lang']))
	setSprache($_GET['lang']);
	
$nation = new nation(); 
$nation->getAll($ohnesperre = true); 
	
$sprache = getSprache(); 
$p=new phrasen($sprache); 

$mobility = new mobilitaetsprogramm(); 
$mobility->getAll(); 

$method =""; 
$breadcrumb = ""; 
if(isset($_GET['method']))
{
	$method = $_GET['method']; 
	$breadcrumb = "> ".ucfirst($method);
}

$zugangscode = $_SESSION['incoming/user']; 

$person = new person(); 
$person->getPersonFromZugangscode($zugangscode); 

$preincoming = new preincoming(); 
$preincoming->loadFromPerson($person->person_id); 

$adresse = new adresse(); 
$adresse->load_pers($person->person_id); 

$kontakt = new kontakt(); 
$kontakt->load_pers($person->person_id); 

$db = new basis_db();

$stsem = new studiensemester();
$stsem->getNextStudiensemester();

$stg = new studiengang();
$stg->getAll();

?>
<html>
	<head>
		<title>Incomming-Verwaltung</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<link href="../../../skin/style.css.php" rel="stylesheet" type="text/css">
	<link href="../../../include/js/tablesort/table.css" rel="stylesheet" type="text/css">
	<script src="../../../include/js/tablesort/table.js" type="text/javascript"></script>
	</head>
	<body>
		<table width="100%" border="0">
			<tr>
				<td align="left" width="33%"><a href="incoming.php">Administration </a> <?php echo $breadcrumb ?> </td>
				<td align="center" width="33%"><?php echo $person->titelpost." ".$person->vorname." ".$person->nachname." ".$person->titelpre?>
				<td align ="right" width="33%"><?php 		
				echo $p->t("global/sprache")." ";
				echo '<a href="'.$_SERVER['PHP_SELF'].'?lang=English">'.$p->t("global/englisch").'</a> | 
				<a href="'.$_SERVER['PHP_SELF'].'?lang=German">'.$p->t("global/deutsch").'</a><br>';?></td>
			</tr>
		</table>
<?php 
if($method =="austauschprogramm")
{
	$zweck = new zweck(); 
	$zweck->getAll(); 	
	
	// Speichert Austauschprogram in preincoming tabelle
	if(isset($_POST['submit_program']))
	{
		$preincoming->result[0]->universitaet = $_REQUEST['universitaet']; 
		$preincoming->result[0]->von = $_REQUEST['von'];; 
		$preincoming->result[0]->bis = $_REQUEST['bis']; 
		$preincoming->result[0]->mobilitaetsprogramm_code = $_REQUEST['austausch_kz']; 
		$preincoming->result[0]->zweck_code = $_REQUEST['zweck']; 
		
		if(!$preincoming->result[0]->save())
			echo $preincoming->errormsg; 
		else 
			echo "Erfolgreich gespeichert."; 
	}	
	// Ausgabe Austauschprogram Formular
	echo '	<form method="POST" action="incoming.php?method=austauschprogramm">
				<table width="30%" border="1" align ="center" style="border-sytle:solid;  border-width:1px; margin-top:10%;">
					<tr>
						<td>Austauschprogramm auswählen </td>
						<td><SELECT name="austausch_kz"> 
						<option value="austausch_auswahl">-- select --</option>';
						foreach ($mobility->result as $mob)
						{
							$selected=""; 
							if($mob->mobilitaetsprogramm_code == $preincoming->result[0]->mobilitaetsprogramm_code)
								$selected = "selected"; 
							echo '<option value="'.$mob->mobilitaetsprogramm_code.'" '.$selected.'>'.$mob->kurzbz."</option>\n";
						}		
		echo '			</td>
					</tr>
					<tr>
						<td>Universität </td>
						<td><input type="text" name="universitaet" size="40" maxlength="256" value="'.$preincoming->result[0]->universitaet.'"></td>
					</tr>
					<tr>
						<td>von </td>
						<td><input type="text" name="von" size="10"  value="'.$preincoming->result[0]->von.'"> yyyy-mm-dd</td>
					</tr>
					<tr>
						<td>bis </td>
						<td><input type="text" name="bis" size="10"  value="'.$preincoming->result[0]->bis.'"> yyyy-mm-dd</td>
					</tr>
					<tr>
						<td>Zweck </td>
						<td><SELECT name="zweck">
						<option value="zweck_auswahl">-- select --</option>';
						foreach ($zweck->result as $zwe)
						{
							$selected="";
							if($preincoming->result[0]->zweck_code == $zwe->zweck_code)
								$selected = "selected"; 
							echo '<option '.$selected.' value="'.$zwe->zweck_code.'" >'.$zwe->bezeichnung."</option>\n";
						}
		echo '			</td>	
					<tr>
						<td colspan="2"><input type="submit" name="submit_program" value="submit"></td>
					</tr>
				</table>
			</form>'; 
}

else if($method=="lehrveranstaltungen")
{
	if(isset($_GET['id']))
	{	// speichern der LV-ID
		if($_GET['mode']=="add")
		{
			$id= $_GET['id']; 
			$preincoming = new preincoming(); 
			$preincoming->loadFromPerson($person->person_id); 
			
			if($preincoming->addLehrveranstaltung($preincoming->result[0]->preincoming_id, $_GET['id'], date('Y-m-d H:i:s')))
				echo "Erfolgreich gespeichert"; 
			else
				echo "Fehler beim Speichern aufgetreten"; 
		}
		// löschen der LV-ID
		if($_GET['mode'] == "delete")
		{
			$id= $_GET['id']; 
			$preincoming = new preincoming(); 
			$preincoming->loadFromPerson($person->person_id); 
			
			if($preincoming->deleteLehrveranstaltung($preincoming->result[0]->preincoming_id, $_GET['id']))
				echo "Erfolgreich gelöscht"; 
			else
				echo "Fehler beim Löschen aufgetreten"; 
		}
	}
	
	// Übersicht der eigenen LVs
	if(isset($_GET['view']))
	{
		if($_GET['view']=="own")
		{
			$lvs = $preincoming->getLehrveranstaltungen($preincoming->result[0]->preincoming_id); 
			echo "<br><br><br>"; 
			echo '<table border ="0" width="100%">
				<tr>
					<td width="25%"></td>
					<td width="25%" align="center"><a href="incoming.php?method=lehrveranstaltungen">Übersicht aller LVs</a></td>
					<td width="25%" align="center"><a href="incoming.php?method=lehrveranstaltungen&view=own">Eigene LVs</a></td>
					<td width="25%"></td>
				</tr>
				<tr><td>&nbsp;</td></tr>
				</table>';
			echo '<table width="90%" border="0" align="center" class="table-autosort:2 table-stripeclass:alternate table-autostripe">
				<thead>
				<tr class="liste">
					<th></th>
					<th class="table-sortable:numeric">ID</th>
					<th class="table-sortable:default">Studiengang</th>
					<th class="table-sortable:numeric">Semester</th>
					<th class="table-sortable:default">Lehrveranstaltung</th>
					<th class="table-sortable:default">Lehrveranstaltung Englisch</th>
					<th>LV-Info</th>
				</tr>
				</thead>
				<tbody>';
			foreach($lvs as $lv)
			{
				$lehrveranstaltung = new lehrveranstaltung(); 
				$lehrveranstaltung->load($lv); 
				echo '<tr>';
				echo '<td> <a href="incoming.php?method=lehrveranstaltungen&mode=delete&id='.$lv.'&view=own">delete</a></td>';
				echo '<td>',$lv,'</td>';
				echo '<td>',$lehrveranstaltung->studiengang_kz,'</td>';
				echo '<td>',$lehrveranstaltung->semester,'</td>';
				echo '<td>',$lehrveranstaltung->bezeichnung,'</td>';
				echo '<td>',$lehrveranstaltung->bezeichnung_english,'</td>';

				echo '<td>
					 </td>';
				echo '</tr>';
			}
			
		}
	}
	// Übersicht aller LVs
	else 
	{
		echo "<br><br><br>"; 
		echo '<table border ="0" width="100%">
				<tr>
					<td width="25%"></td>
					<td width="25%" align="center"><a href="incoming.php?method=lehrveranstaltungen">Übersicht aller LVs</a></td>
					<td width="25%" align="center"><a href="incoming.php?method=lehrveranstaltungen&view=own">Eigene LVs</a></td>
					<td width="25%"></td>
				</tr>
				<tr><td>&nbsp;</td></tr>
				</table>';
		
		$qry = "SELECT 
					tbl_lehrveranstaltung.lehrveranstaltung_id, tbl_lehrveranstaltung.studiengang_kz, 
					tbl_lehrveranstaltung.bezeichnung, tbl_lehrveranstaltung.semester, 
					tbl_lehrveranstaltung.bezeichnung_english, tbl_lehrveranstaltung.incoming,
					(
					Select count(*) 
					FROM (
						SELECT
							person_id
						FROM 
							campus.vw_student_lehrveranstaltung 
						JOIN public.tbl_benutzer using(uid)
						JOIN public.tbl_student ON(uid=student_uid) 
						JOIN public.tbl_prestudentstatus USING(prestudent_id)
						WHERE
							lehrveranstaltung_id=tbl_lehrveranstaltung.lehrveranstaltung_id 
							AND
							lehreinheit_id in (SELECT lehreinheit_id FROM lehre.tbl_lehreinheit 
						WHERE lehrveranstaltung_id=tbl_lehrveranstaltung.lehrveranstaltung_id						
							AND 
							tbl_lehreinheit.studiensemester_kurzbz='$stsem->studiensemester_kurzbz')
							AND
							tbl_prestudentstatus.status_kurzbz='Incoming'
							AND tbl_prestudentstatus.status_kurzbz='$stsem->studiensemester_kurzbz'
						UNION
						SELECT 
							person_id 
						FROM 
							public.tbl_preincoming_lehrveranstaltung 
						JOIN public.tbl_preincoming using(preincoming_id) 
						WHERE lehrveranstaltung_id=tbl_lehrveranstaltung.lehrveranstaltung_id 
						AND 
						(von is null OR von >= '$stsem->start') 
						AND 
						(bis is null OR bis <= '$stsem->ende') 
						AND aktiv = true				
						)a ) as anzahl
					FROM 
						lehre.tbl_lehrveranstaltung JOIN public.tbl_studiengang USING(studiengang_kz)
					WHERE 
						tbl_lehrveranstaltung.incoming>0 AND 
						tbl_lehrveranstaltung.aktiv AND 
						tbl_lehrveranstaltung.lehre
						AND tbl_lehrveranstaltung.studiengang_kz>0 AND tbl_lehrveranstaltung.studiengang_kz<10000
						AND tbl_studiengang.aktiv order by studiengang_kz
					";
	
		echo '	<form action="incoming.php?method=lehrveranstaltungen" method="POST">
				<table width="90%" border="0" align="center" class="table-autosort:2 table-stripeclass:alternate table-autostripe">
				<thead>
				<tr class="liste">
					<th></th>
					<th class="table-sortable:numeric">ID</th>
					<th class="table-sortable:default">Studiengang</th>
					<th class="table-sortable:numeric">Semester</th>
					<th class="table-sortable:default">Lehrveranstaltung</th>
					<th class="table-sortable:default">Lehrveranstaltung Englisch</th>
					<th>LV-Info</th>
					<th class="table-sortable:numeric">Freie Pl&auml;tze</th>
				</tr>
				</thead>
				<tbody>';
		if($result = $db->db_query($qry))
		{
			while($row = $db->db_fetch_object($result))
			{
				$freieplaetze = $row->incoming - $row->anzahl;
				if($freieplaetze<0)
					$freieplaetze=0;
				
				echo '<tr>';
				if(!$preincoming->checkLehrveranstaltung($preincoming->result[0]->preincoming_id, $row->lehrveranstaltung_id))
					echo '<td><a href="incoming.php?method=lehrveranstaltungen&mode=add&id='.$row->lehrveranstaltung_id.'">add</a></td>';
				else
					echo '<td>added </td>';
				echo '<td>',$row->lehrveranstaltung_id,'</td>';
				echo '<td>',$stg->kuerzel_arr[$row->studiengang_kz],'</td>';
				echo '<td>',$row->semester,'</td>';
				echo '<td>',$row->bezeichnung,'</td>';
				echo '<td>',$row->bezeichnung_english,'</td>';
				echo '<td>
						<a href="#Deutsch" class="Item" onclick="javascript:window.open(\'ects/preview.php?lv='.$row->lehrveranstaltung_id.'&amp;language=de\',\'Lehrveranstaltungsinformation\',\'width=700,height=750,resizable=yes,menuebar=no,toolbar=no,status=yes,scrollbars=yes\');return false;">Deutsch&nbsp;</a>
						<a href="#Englisch" class="Item" onclick="javascript:window.open(\'ects/preview.php?lv='.$row->lehrveranstaltung_id.'&amp;language=en\',\'Lehrveranstaltungsinformation\',\'width=700,height=750,resizable=yes,menuebar=no,toolbar=no,status=yes,scrollbars=yes\');return false;">Englisch</a>
					  </td>';
				echo '<td>',$freieplaetze,'</td>';
				echo '</tr>';
			}
		}
		
		echo '</tbody></table>';
	}
}
// Benutzerprofil bearbeiten
else if ($method == "profil")
{	
	// Profil speichern
	if(isset($_POST['submit_profil']))
	{
		$save = true; 
		
		$person->titelpost = $_REQUEST['titel_post']; 
		$person->vorname = $_REQUEST['vorname']; 
		$person->nachname = $_REQUEST['nachname']; 
		$person->titelpre = $_REQUEST['titel_pre']; 
		$person->gebdatum = $_REQUEST['geb_datum']; 
		$person->staatsbuergerschaft = $_REQUEST['staatsbuerger']; 
		$person->anmerkungen = $_REQUEST['anmerkung']; 
		$person->geschlecht = $_REQUEST['geschlecht']; 
		$person->aktiv = true; 
		$person->new = false; 
		
		if(!$person->save())
		{
			echo $person->errormsg; 
			$save = false; 
		}
	
		$adresse->result[0]->strasse = $_REQUEST['strasse']; 
		$adresse->result[0]->plz = $_REQUEST['plz']; 
		$adresse->result[0]->ort = $_REQUEST['ort']; 
		$adresse->result[0]->nation = $_REQUEST['nation']; 
		$adresse->result[0]->heimatadresse = true; 
		$adresse->result[0]->zustelladresse = true; 
		$adresse->result[0]->new = false; 

		if(!$adresse->result[0]->save())
		{
			echo $adresse->errormsg;
			$save = false; 
		}

		foreach($kontakt->result as $kon)
		{
			if($kon->kontakttyp=="email")
			{
				$kon->kontakt = $_REQUEST['email']; 
				$kontakt->new = false; 
				if(!$kon->save())
				{
					echo 'Fehler beim Anlegen des Kontaktes aufgetreten';
					$save = false; 
				}
			}
		}
		if($save)
			echo "Erfolgreich gespeichert"; 
	}
	// Ausgabe Profil Formular
	echo'<form action="incoming.php?method=profil" method="POST">
		<table border = "1" align="center" style="margin-top:10%;">
			<tr>
				<td>'.$p->t('global/titel').' Post</td>
				<td><input type="text" size="20" maxlength="32" name="titel_post" value="'.$person->titelpost.'"></td>
			</tr>
			<tr>
				<td>'.$p->t('global/vorname').'</td>
				<td><input type="text" size="40" maxlength="32" name="vorname" value="'.$person->vorname.'"></td>
			</tr>
			<tr>
				<td>'.$p->t('global/nachname').'</td>
				<td><input type="text" size="40" maxlength="64" name="nachname" value="'.$person->nachname.'"></td>
			</tr>
			<tr>
				<td>'.$p->t('global/titel').' Pre</td>
				<td><input type="text" size="20" maxlength="64" name="titel_pre" value="'.$person->titelpre.'"></td>
			</tr>
			<tr>
				<td>'.$p->t('global/geburtsdatum').'</td>
				<td><input type="text" size="20" name="geb_datum" value="'.$person->gebdatum.'" onfocus="this.value="""; ></td>
			</tr>
			<tr>
				<td>'.$p->t('global/staatsbuergerschaft').'</td>

				<td><SELECT name="staatsbuerger">
				<option value="staat_auswahl">-- select --</option>';
				foreach ($nation->nation as $nat)
				{
					$selected="";
					if($person->staatsbuergerschaft == $nat->code)
						$selected = "selected"; 
					echo '<option '.$selected.' value="'.$nat->code.'" >'.$nat->langtext."</option>\n";
				}
	
	echo'	</td></tr>		
			<tr>
				<td>'.$p->t('global/geschlecht').'</td>';
	if($person->geschlecht == "m")
		echo '
				<td>    <input type="radio" name="geschlecht" value="m" checked> '.$p->t('global/mann').'
    					<input type="radio" name="geschlecht" value="w">'.$p->t('global/frau').'
    			</td>';
		else 
			echo '
				<td>    <input type="radio" name="geschlecht" value="m"> '.$p->t('global/mann').'
    					<input type="radio" name="geschlecht" value="w" checked>'.$p->t('global/frau').'
    			</td>';
			
	echo'	</tr>	
			<tr>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td>'.$p->t('global/strasse').'</td>
				<td><input type="text" size="40" maxlength="256" name="strasse" value="'.$adresse->result[0]->strasse.'"></td>
			</tr>	
			<tr>
				<td>'.$p->t('global/plz').'</td>
				<td><input type="text" size="20" maxlength="16" name="plz" value="'.$adresse->result[0]->plz.'"></td>
			</tr>				
			<tr>
				<td>'.$p->t('global/ort').'</td>
				<td><input type="text" size="40" maxlength="256" name="ort" value="'.$adresse->result[0]->ort.'"></td>
			</tr>				
			<tr>
				<td>Nation</td>
				<td><SELECT name="nation">
				<option value="nat_auswahl">-- select --</option>';
				foreach ($nation->nation as $nat)
				{
					$selected="";
					if($adresse->result[0]->nation == $nat->code)
						$selected = "selected"; 
					echo '<option '.$selected.' value="'.$nat->code.'" >'.$nat->langtext."</option>\n";
				}
							
	echo '	</tr>				
			<tr>
				<td>E-Mail</td>'; 
	foreach($kontakt->result as $kon)
	{
		if($kon->kontakttyp == "email")
		{
			$email = $kon->kontakt;
		}
	}
	echo'	<td><input type="text" size="40" maxlength="128" name="email" value="'.$email.'"></td>
			</tr>	
			<tr>
				<td>'.$p->t('global/anmerkung').'</td>
				<td><textarea name="anmerkung" cols="31" rows="5">'.$person->anmerkungen.'</textarea></td>
			</tr>	
			<tr>
				<td colspan="2" align = "center"><input type="submit" name="submit_profil" value="Submit"></td>		
			</tr>';
	echo '	<tr><td><input type="hidden" name="zugangscode" value='.uniqid().'></td></tr>	
		</table>
	</form>';
}

// Ausgabe Menü
else 
{
	echo "<br><br><br><br>"; 
	echo "<table align ='center' width ='50%' border='2'>";
	echo "		<tr>
					<td><a href ='incoming.php?method=austauschprogramm '>Austauschprogramm</a></td><td></td>"; 	
	echo '		</tr>
				<tr>	
					<td><a href="incoming.php?method=profil">Persönliche Daten ändern</a></td>
				</tr>
				<tr>
					<td><a href="incoming.php?method=lehrveranstaltungen">Lehrveranstaltungen auswählen</a></td>
				</tr>
				<tr>
					<td><a href="incoming.php">Learning Agreement erstellen</a></td>
				</tr>
				<tr>
					<td><a href="incoming.php">'.$p->t("incoming/uploadvondateien").'</a></td>
				</tr>
			</table>
			<table width="100%" border="0">
				<tr>
					<td align="center"><a href="logout.php">Logout</a> </td>
				</tr>
			</table>';
}
?>
	</body>
</html>