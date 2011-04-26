<?php
/* Copyright (C) 2011 FH Technikum-Wien
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
 * Authors: Christian Paminger 		< christian.paminger@technikum-wien.at >
 *          Andreas Oesterreicher 	< andreas.oesterreicher@technikum-wien.at >
 *          Karl Burkhart 			< burkhart@technikum-wien.at >
 */
require_once('../../config/vilesci.config.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/benutzerberechtigung.class.php');
require_once('../../include/preincoming.class.php');
require_once('../../include/person.class.php');
require_once('../../include/adresse.class.php');
require_once('../../include/kontakt.class.php');
require_once('../../include/nation.class.php');
require_once('../../include/studiengang.class.php');
require_once('../../include/mobilitaetsprogramm.class.php');
require_once('../../include/zweck.class.php');
require_once('../../include/akte.class.php');
require_once('../../include/lehrveranstaltung.class.php');
require_once('../../include/benutzer.class.php');
require_once('../../include/prestudent.class.php');
require_once('../../include/student.class.php');
require_once('../../include/lehrverband.class.php');
require_once('../../include/bisio.class.php');
require_once('../../include/'.EXT_FKT_PATH.'/generateuid.inc.php');

$user = get_uid();

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);
$db = new basis_db();

$datum_obj = new datum();
$message='';
echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
        "http://www.w3.org/TR/html4/strict.dtd">
<html>
	<head>
		<title>Incoming</title>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<link rel="stylesheet" href="../../skin/tablesort.css" type="text/css"/>
		<link rel="stylesheet" href="../../skin/jquery.css" type="text/css"/>
		<link rel="stylesheet" href="../../skin/fhcomplete.css" type="text/css">
		<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">		
		<script type="text/javascript" src="../../include/js/jquery.js"></script>
				
		<script type="text/javascript">
	
		$(document).ready(function() 
			{ 
			    $("#myTable").tablesorter(
				{
					sortList: [[2,0]],
					widgets: ["zebra"]
				}); 
			} 
		); 
			
	</script> 
	</head>
	<body>
	';

if(!$rechte->isBerechtigt('inout/incoming', null, 'suid'))
	die('Sie haben keine Berechtigung fuer diese Seite');

$preincoming_id = isset($_GET['preincoming_id'])?$_GET['preincoming_id']:null;
$action = isset($_GET['action'])?$_GET['action']:'personendetails';
$method = isset($_GET['method'])?$_GET['method']:null;

if($preincoming_id=='')
	exit;
	
if($method!='')
{
	switch($method)
	{
		case 'saveperson':
			//Speichern der Personendetails
			if(isset($_POST['person_id']) && isset($_POST['adresse_id']) && isset($_POST['kontakt_id']) &&
			   isset($_POST['titelpre']) && isset($_POST['titelpost']) && isset($_POST['vorname']) &&
			   isset($_POST['nachname']) && isset($_POST['anmerkung']) && isset($_POST['strasse']) &&
			   isset($_POST['plz']) && isset($_POST['ort']) && isset($_POST['nation']) &&
			   isset($_POST['email']) && isset($_POST['universitaet']) && isset($_POST['mobilitaetsprogramm']) &&
			   isset($_POST['zweck']) && isset($_POST['von']) && isset($_POST['bis']))
			{
				$person_id = $_POST['person_id'];
				$adresse_id = $_POST['adresse_id'];
				$kontakt_id = $_POST['kontakt_id'];
				$titelpre = $_POST['titelpre'];
				$titelpost = $_POST['titelpost'];
				$vorname = $_POST['vorname'];
				$nachname = $_POST['nachname'];
				$anmerkung = $_POST['anmerkung'];
				$strasse = $_POST['strasse'];
				$plz = $_POST['plz'];
				$ort = $_POST['ort'];
				$nation = $_POST['nation'];
				$email = $_POST['email'];
				$universitaet = $_POST['universitaet'];
				$mobilitaetsprogramm = $_POST['mobilitaetsprogramm'];
				$zweck = $_POST['zweck'];
				$von = $_POST['von'];
				$bis = $_POST['bis'];
				$aktiv = isset($_POST['aktiv']);
				
				//Person
				$person = new person();
				if($person->load($person_id))
				{
					$person->titelpre = $titelpre;
					$person->titelpost = $titelpost;
					$person->nachname = $nachname;
					$person->vorname = $vorname;
					if(!$person->save(false))
						$message.='<span class="error">'.$person->errormsg.'</span>';
				}
				//Adresse
				$adresse = new adresse();
				if($adresse_id=='')
				{
					$adresse->new = true;
					$adresse->insertamum = date('Y-m-d H:i:s');
					$adresse->insertvon = $user;
					$adresse->heimatadresse = true;
					$adresse->zustelladresse = true;
				}
				else
				{
					$adresse->load($adresse_id);
					$adresse->new = false;
				}
					
				$adresse->strasse = $strasse;
				$adresse->plz = $plz;
				$adresse->ort = $ort;
				$adresse->nation = $nation;
				$adresse->updateamum = date('Y-m-d H:i:s');
				$adresse->updatevon = $user;
				
				if(!$adresse->save())
					$message.='<span class="error">'.$adresse->errormsg.'</span>';
				
				//E-Mail
				$kontakt = new kontakt();
				
				if($kontakt_id=='')
				{
					$kontakt->new = true;
					$kontakt->insertamum = date('Y-m-d H:i:s');
					$kontakt->insertvon = $user;
					$kontakt->zustellung = true;
				}
				else
				{
					$kontakt->load($kontakt_id);
					$kontakt->new = false;
				}
				
				$kontakt->kontakt = $email;
				$kontakt->kontakttyp = 'email';
				$kontakt->updateamum = date('Y-m-d H:i:s');
				$kontakt->updatevon = $user;
				
				if(!$kontakt->save())
					$message.='<span class="error">'.$kontakt->errormsg.'</span>';
				
				//PreIncoming
				$inc = new preincoming();
				
				if($inc->load($preincoming_id))
				{
					$inc->mobilitaetsprogramm_code = $mobilitaetsprogramm;
					$inc->zweck_code = $zweck;
					$inc->universitaet = $universitaet;
					$inc->anmerkung = $anmerkung;
					$inc->aktiv = $aktiv;
					$inc->von = $datum_obj->formatDatum($von);
					$inc->bis = $datum_obj->formatDatum($bis);
					$inc->updateamum = date('Y-m-d H:i:s');
					$inc->updatevon = $user;
					$inc->new = false;
					
					if(!$inc->save())
						$message.='<span class="error">'.$inc->errormsg.'</span>';
				}
				else
					$message.='<span class="error">'.$inc->errormsg.'</span>';
			}
			if($message=='')
				$message.='<span class="ok">Daten wurden gespeichert</span>';
			break;
		case 'fasuebernahme':
			/* Uebernahme eines PreIncoming ins FAS 
			 * - Prestudent anlegen
			 * - Prestudent Status anlegen
			 * - Benutzer anlegen
			 * - Student anlegen
			 * - Lehrverbandsgruppe anlegen und zuordnen
			 * - Mobilitaetsprogramm
			 * - Uebernommen Boolean setzen
			 * 
			 * 
			 * Offene Punkte:
			 * - Automatische Zuteilung zu den LVs
			 * - E-Mail benachrichtigung an Assistenz
			 */ 
			$error=false;
			$errormsg='';
			
			$inc = new preincoming();
			if($inc->load($preincoming_id))
			{
				$person = new person();
				if($person->load($inc->person_id))
				{
					
					//TODO:
					$studiensemester_kurzbz = getStudiensemesterFromDatum($inc->von);
					$ausbildungssemester=1;
					$orgform_kurzbz='';
					$studiengang_kz=$_POST['studiengang_kz'];
					
					// Prestudent
					$prestudent = new prestudent();
					$prestudent->person_id = $inc->person_id;
					$prestudent->new = true;
					$prestudent->aufmerksamdurch_kurzbz = 'k.A.';
					$prestudent->studiengang_kz = $studiengang_kz;
					$prestudent->reihungstestangetreten = false;
					$prestudent->bismelden = true;
					
					if(!$prestudent->save())
					{
						$error=true;
						$errormsg = $prestudent->errormsg;
					}
							
					if(!$error)
					{
						//Prestudent Rolle Anlegen			
						$rolle = new prestudent();
				
						$rolle->prestudent_id = $prestudent->prestudent_id;
						$rolle->status_kurzbz = 'Incoming';
						$rolle->studiensemester_kurzbz = $studiensemester_kurzbz;
						$rolle->ausbildungssemester = $ausbildungssemester;
						$rolle->orgform_kurzbz = $orgform_kurzbz;
						$rolle->datum = date('Y-m-d');
						$rolle->insertamum = date('Y-m-d H:i:s');
						$rolle->insertvon = $user;
				
						$rolle->new = true;
				
						if(!$rolle->save_rolle())
						{
							$error = true;
							$errormsg = $rolle->errormsg;
						}
						else
							$error = false;
					}
				
					if(!$error)
					{
						//Matrikelnummer und UID generieren
						$matrikelnr = generateMatrikelnummer($studiengang_kz, $studiensemester_kurzbz);
								
						$jahr = mb_substr($matrikelnr,0, 2);
						$stg = mb_substr($matrikelnr, 3, 4);
						
						$stg_obj = new studiengang();
						$stg_obj->load(ltrim($stg,'0'));
						
						$uid = generateUID($stg_obj->kurzbz,$jahr, $stg_obj->typ, $matrikelnr);
										
						//Benutzerdatensatz anlegen
						$benutzer = new benutzer();
						$benutzer->uid = $uid;
						$benutzer->person_id = $inc->person_id;
						$benutzer->aktiv = true;
						
						$nachname_clean = mb_strtolower(convertProblemChars($person->nachname));
						$vorname_clean = mb_strtolower(convertProblemChars($person->vorname));
						$nachname_clean = str_replace(' ','_', $nachname_clean);
						$vorname_clean = str_replace(' ','_', $vorname_clean);
						
						$qry_alias = "SELECT * FROM public.tbl_benutzer WHERE alias=LOWER('".$vorname_clean.".".$nachname_clean."')";
						$result_alias = $db->db_query($qry_alias);
						if($db->db_num_rows($result_alias)==0)								
							$benutzer->alias =$vorname_clean.'.'.$nachname_clean;
						else 
							$benutzer->alias = '';
													
						$benutzer->insertamum = date('Y-m-d H:i:s');
						$benutzer->insertvon = $user;
										
						if($benutzer->save(true, false))
						{
							//Studentendatensatz anlegen
							$student = new student();
							$student->uid = $uid;
							$student->matrikelnr = $matrikelnr;
							$student->prestudent_id = $prestudent->prestudent_id;
							$student->studiengang_kz = $studiengang_kz;
							$student->semester = '0';
							$student->verband = 'I';
							$student->gruppe = ' ';
							$student->insertamum = date('Y-m-d H:i:s');
							$student->insertvon = $user;
				
							$lvb = new lehrverband();
							if(!$lvb->exists($student->studiengang_kz, $student->semester, $student->verband, $student->gruppe))
							{
								$lvb->studiengang_kz = $student->studiengang_kz;
								$lvb->semester = $student->semester;
								$lvb->verband = $student->verband;
								$lvb->gruppe = $student->gruppe;
								$lvb->bezeichnung = 'Incoming';
								$lvb->aktiv = true;
								
								$lvb->save(true);
							}
							
							if($student->save(true, false))
							{
								//StudentLehrverband anlegen
								$studentlehrverband = new student();
								$studentlehrverband->uid = $uid;
								$studentlehrverband->studiensemester_kurzbz = $studiensemester_kurzbz;
								$studentlehrverband->studiengang_kz = $studiengang_kz;
								$studentlehrverband->semester = '0';
								$studentlehrverband->verband = 'I';
								$studentlehrverband->gruppe = ' ';
								$studentlehrverband->insertamum = date('Y-m-d H:i:s');
								$studentlehrverband->insertvon = $user;
									
								if(!$studentlehrverband->save_studentlehrverband(true))
								{
									$error = true;
									$errormsg = 'StudentLehrverband konnte nicht angelegt werden';
								}
							}
							else 
							{
								$error = true;
								$errormsg = 'Student konnte nicht angelegt werden: '.$student->errormsg;
							}
						}
						else 
						{
							$error = true;
							$errormsg = 'Benutzer konnte nicht angelegt werden:'.$benutzer->errormsg;
						}
						
						if(!$error)
						{
							
							// I/O Datensatz wird nur erstellt, wenn die noetigen Daten vorhanden sind
							if($inc->mobilitaetsprogramm_code!='' && $inc->zweck_code!='' 
								&& $inc->von!='' && $inc->bis!='' )
							{
								$bisio = new bisio();
								
								$adresse = new adresse();
								$adresse->load_pers($inc->person_id);
								$nation_code='';
								if(isset($adresse->result[0]))
									$nation_code = $adresse->result[0]->nation;
								if($nation_code=='')
									$nation_code='A';
								
								$bisio->new = true;
								$bisio->mobilitaetsprogramm_code = $inc->mobilitaetsprogramm_code;
								$bisio->nation_code = $nation_code;
								$bisio->von = $inc->von;
								$bisio->bis = $inc->bis;
								$bisio->zweck_code = $inc->zweck_code;
								$bisio->student_uid = $uid;
								$bisio->updateamum = date('Y-m-d H:i:s');
								$bisio->updatevon = $user;
								$bisio->insertamum = date('Y-m-d H:i:s');
								$bisio->insertvon = $user;
					
								if(!$bisio->save())
								{
									$error=true;
									$errormsg = 'Fehler beim Uebernehmen des IO Datensatzes';
								}
							}
							
							$inc->new=false;
							$inc->uebernommen=true;
							$inc->save();
							
						}
					}
					else
					{
						$error = true;
						$errormsg = 'Person konnte nicht geladen werden:'.$person->errormsg;
					}
				}
				
				if(!$error)
				{
					$db->db_query('COMMIT');
					$message.='<span class="ok">Uebernahme erfolgreich</span';
				}
				else
				{
					$db->db_query('ROLLBACK');
					$message.='<span class="error">'.$errormsg.'</span>';
				}
			}
			break;
		default:
			break;
	}
}
$inc = new preincoming();
if(!$inc->load($preincoming_id))
	$message.= '<span class="error">'.$inc->errormsg.'</span>';
$person = new person();
if(!$person->load($inc->person_id))
	$message.='<span class="error">'.$person->errormsg.'</span>';

echo '<h2>Details - '.$person->vorname.' '.$person->nachname.'</h2>';

print_menu('Personendetails', 'personendetails');
echo ' | ';
print_menu('Dokumente', 'dokumente');
echo ' | ';
print_menu('Lehrveranstaltungen', 'lehrveranstaltungen');
echo '<div style="float:right">'.$message.'</div>';
echo '<br />';
switch($action)
{
	case 'personendetails':
		print_personendetails();
		break;
	case 'dokumente':
		print_dokumente();
		break;
	case 'lehrveranstaltungen':
		print_lehrveranstaltungen();
		break;
	default:
		break;
}
echo '</body>';
echo '</html>';

/*********** FUNKTIONEN *********************/
/**
 * Erstellt einen MenuLink
 * @param $name Name des Links
 * @param $value Action
 */
function print_menu($name, $value)
{
	global $action, $preincoming_id;
	if($value==$action)
		$name = '<b>'.$name.'</b>';
	echo '<a href="'.$_SERVER['PHP_SELF'].'?action='.$value.'&amp;preincoming_id='.$preincoming_id.'">'.$name.'</a>';
}

/**
 * Erstellt den Tab zur Anzeige der Personendetails
 */
function print_personendetails()
{
	global $person, $inc, $preincoming_id, $datum_obj;
	
	$adresse = new adresse();
	$adresse->load_pers($person->person_id);
	if(isset($adresse->result[0]))
		$adresse = $adresse->result[0];
		
	$kontakt = new kontakt();
	$kontakt->load_pers($person->person_id);
	if(isset($kontakt->result[0]))
		$kontakt = $kontakt->result[0];
	
	echo '<fieldset>';
	echo '<form action="'.$_SERVER['PHP_SELF'].'?action=personendetails&method=saveperson&preincoming_id='.$preincoming_id.'" method="POST">';
	echo '<input type="hidden" name="person_id" value="'.$person->person_id.'">';
	echo '<input type="hidden" name="adresse_id" value="'.$adresse->adresse_id.'">';
	echo '<input type="hidden" name="kontakt_id" value="'.$kontakt->kontakt_id.'">';
	echo '<table>
			<tr>
				<td>TitelPre</td>
				<td><input type="text" name="titelpre" size="10" value="'.$person->titelpre.'"></td>
				<td></td>
				<td>Strasse</td>
				<td colspan="3"><input type="text" name="strasse" size="50" value="'.$adresse->strasse.'"></td>
			</tr>
			<tr>
				<td>Vorname</td>
				<td><input type="text" name="vorname" size="30" value="'.$person->vorname.'"></td>
				<td></td>
				<td>Plz / Ort</td>
				<td colspan="3">
					<input type="text" size="5" name="plz" value="'.$adresse->plz.'">
					<input type="text" name="ort" size="40" value="'.$adresse->ort.'">
				</td>
			</tr>
			<tr>
				<td>Nachname</td>
				<td><input type="text" name="nachname" size="30" value="'.$person->nachname.'"></td>
				<td></td>
				<td>Nation</td>
				<td colspan="3">
					<SELECT name="nation">
					<OPTION value="">-- keine Auswahl --</OPTION>
					';
	$nation = new nation();
	$nation->getAll();
	foreach($nation->nation as $row)
	{
		if($adresse->nation==$row->code)
			$selected='selected';
		else
			$selected='';
		
		echo '<OPTION value="'.$row->code.'" '.$selected.'>'.$row->kurztext.'</OPTION>';
	}
	echo '
					</SELECT>
				</td>
			</tr>
			<tr>
				<td>TitelPost</td>
				<td><input type="text" name="titelpost" size="10" value="'.$person->titelpost.'"></td>
				<td></td>
				<td>E-Mail</td>
				<td colspan="3"><input type="text" name="email" size="50" value="'.$kontakt->kontakt.'"></td>
			</tr>
			<tr>
				<td>Anmerkungen</td>
				<td colspan="6"><textarea name="anmerkung" rows="4" cols="65">'.$inc->anmerkung.'</textarea></td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
			</tr>
			<tr>
				<td>Universität</td>
				<td colspan="4"><input type="text" name="universitaet" size="50" value="'.$inc->universitaet.'"></td>
								
				<td>Aktiv</td>
				<td><input type="checkbox" name="aktiv" '.($inc->aktiv?'checked':'').'></td>
			</tr>
			<tr>
				<td>Mobilitätsprogramm</td>
				<td><SELECT name="mobilitaetsprogramm">
						<OPTION value="">-- keine Auswahl --</OPTION>';

	$mob = new mobilitaetsprogramm();
	$mob->getAll();
	
	foreach($mob->result as $row)
	{
		if($row->mobilitaetsprogramm_code==$inc->mobilitaetsprogramm_code)
			$selected='selected';
		else
			$selected='';
		
		echo '<OPTION value="'.$row->mobilitaetsprogramm_code.'" '.$selected.'>'.$row->kurzbz.'</OPTION>';
	}
	echo '</SELECT>
				</td>
				<td></td>
				<td>Von</td>
				<td>
					<input type="text" id="von" size="10" name="von" value="'.$datum_obj->formatDatum($inc->von,'d.m.Y').'">
					<script type="text/javascript">
					$(document).ready(function() 
					{ 
					    $( "#von" ).datepicker($.datepicker.regional["de"]);
					});
					</script>								
				</td>
				<td>Zugangscode:</td>
				<td><b>'.$person->zugangscode.'</b></td>
			</tr>
			<tr>
				<td>Zweck</td>
				<td><SELECT name="zweck">
						<OPTION value="">-- keine Auswahl --</OPTION>';
	
	$zweck = new zweck();
	$zweck->getAll();
	
	foreach($zweck->result as $row)
	{
		if($row->zweck_code==$inc->zweck_code)
			$selected='selected';
		else
			$selected='';
		
		echo '<OPTION value="'.$row->zweck_code.'" '.$selected.'>'.$row->bezeichnung.'</OPTION>';
	}
	
	echo '</SELECT>
				</td>
				<td></td>
				<td>Bis</td>
				<td>
					<input type="text" name="bis" id="bis" size="10" value="'.$datum_obj->formatDatum($inc->bis,'d.m.Y').'">
					<script type="text/javascript">
					$(document).ready(function() 
					{ 
					    $( "#bis" ).datepicker($.datepicker.regional["de"]);
					});
					</script>		
				</td>
				
				<td></td>
				<td><input type="submit" name="save" value="Speichern"></td>
			</tr>
		</table>
			';
	echo '</form>';
	echo '</fieldset>';
	echo '
	<br>
	<fieldset>
		<legend>Übernahme ins FAS</legend>';
	if($inc->uebernommen)
	{
		echo '<b>Diese Person wurde bereits ins FAS übernommen</b>';
	}
	else
	{
		echo '<form action="'.$_SERVER['PHP_SELF'].'?action=personendetails&amp;method=fasuebernahme&amp;preincoming_id='.$preincoming_id.'" method="POST">';
		echo 'Incoming für den Studiengang: <SELECT name="studiengang_kz">';
		$stg = new studiengang();
		$stg->getAll('typ, kurzbz');
		
		foreach($stg->result as $row)
		{
			echo '<OPTION value="'.$row->studiengang_kz.'">'.$row->kuerzel.' ('.$row->kurzbzlang.') '.$row->bezeichnung.'</OPTION>';
		}
		echo '</SELECT>';
		echo ' <input type="submit" name="uebernahme" value="Übernehmen">';
		echo '</form>';
	}		
	echo '</fieldset>';
	
}

function print_dokumente()
{
	global $person, $preincoming_id, $datum_obj;
	
	echo '<fieldset>';
	$akte = new akte();
	$akte->getAkten($person->person_id);
	
	echo '
	Folgende Dokumente wurden hochgeladen:<br><br>
	<script type="text/javascript">
	$(document).ready(function() 
		{ 
		    $("#dokumente").tablesorter(
			{
				sortList: [[0,0]],
				widgets: ["zebra"]
			}); 
		} 
	); 
	</script>
	<table class="tablesorter" id="dokumente">
		<thead>
			<tr>
				<th>Datum</th>
				<th>Name</th>
				<th>Typ</th>
			</tr>
		</thead>
		<tbody>
		';
	foreach($akte->result as $row)
	{
		echo '<tr>';
		echo '<td>'.$datum_obj->formatDatum($row->erstelltam,'d.m.Y').'</td>';
		echo '<td><a href="../../content/akte.php?id='.$row->akte_id.'">'.$row->titel.'</a></td>';
		echo '<td>'.$row->dokument_kurzbz.'</td>';
		echo '</tr>';
		
	}
	echo '</tbody></table>';
	echo '</fieldset>';
}

function print_lehrveranstaltungen()
{
	global $person, $inc, $preincoming_id, $datum_obj;
	
	echo '<fieldset>
	Die Person hat sich zu folgenden LVs angemeldet:<br><br>';
	$ids = $inc->getLehrveranstaltungen($preincoming_id);
	
	$stg = new studiengang();
	$stg->getAll();
		
	$lv = new lehrveranstaltung();
	$lv->loadArray($ids);
				
	echo '
	<script type="text/javascript">
	$(document).ready(function() 
		{ 
		    $("#lehrveranstaltungen").tablesorter(
			{
				sortList: [[0,0]],
				widgets: ["zebra"]
			}); 
		} 
	); 
	</script>
	<table class="tablesorter" id="lehrveranstaltungen">
		<thead>
			<tr>
				<th>Bezeichnung</th>
				<th>Studiengang</th>
				<th>Semester</th>
			</tr>
		</thead>
		<tbody>';
	foreach($lv->lehrveranstaltungen as $row)
	{
		echo '<tr>';
		echo '<td>'.$row->bezeichnung.'</td>';
		echo '<td>'.$stg->kuerzel_arr[$row->studiengang_kz].'</td>';
		echo '<td>'.$row->semester.'. Semester</td>';
		echo '</tr>';
	}
	if($inc->bachelorthesis)
		echo '<tr><td>Bachelor Thesis</td></tr>';
	if($inc->masterthesis)
		echo '<tr><td>Master Thesis</td></tr>';
	echo '</tbody></table>';
	echo '</fieldset>';
}


// ****
// * Generiert die Matrikelnummer
// * FORMAT: 0710254001
// * 07 = Jahr
// * 1/2/0  = WS/SS/incoming
// * 0254 = Studiengangskennzahl vierstellig
// * 001 = Laufende Nummer
// ****
function generateMatrikelnummer($studiengang_kz, $studiensemester_kurzbz)
{ 
	$db = new basis_db();
	
	$jahr = mb_substr($studiensemester_kurzbz, 4);	
	$sem = mb_substr($studiensemester_kurzbz, 0, 2);
	if($sem=='SS')
		$jahr = $jahr-1;
	$art =0;
	
	$matrikelnummer = sprintf("%02d",$jahr).$art.sprintf("%04d",$studiengang_kz);
	
	$qry = "SELECT matrikelnr FROM public.tbl_student WHERE matrikelnr LIKE '$matrikelnummer%' ORDER BY matrikelnr DESC LIMIT 1";
	
	if($db->db_query($qry))
	{
		if($row = $db->db_fetch_object())
		{
			$max = mb_substr($row->matrikelnr,7);
		}
		else 
			$max = 0;
		
		$max += 1;
		return $matrikelnummer.sprintf("%03d",$max);
	}
	else 
	{
		return false;
	}
}

function clean_string($string)
 {
 	$trans = array("ä" => "ae",
 				   "Ä" => "Ae",
 				   "ö" => "oe",
 				   "Ö" => "Oe",
 				   "ü" => "ue",
 				   "Ü" => "Ue",
 				   "á" => "a",
 				   "à" => "a",
 				   "é" => "e",
 				   "è" => "e",
 				   "ó" => "o",
 				   "ò" => "o",
 				   "í" => "i",
 				   "ì" => "i",
 				   "ù" => "u",
 				   "ú" => "u",
 				   "ß" => "ss");
	$string = strtr($string, $trans);
    return ereg_replace("[^a-zA-Z0-9]", "", $string);
    //[:space:]
 }

?>