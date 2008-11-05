<?php
/* Copyright (C) 2006 Technikum-Wien
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
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>.
 */

require_once('../../../config.inc.php');
require_once('../../../../include/functions.inc.php');
require_once('../../../../include/lehrveranstaltung.class.php');
require_once('../../../../include/studiengang.class.php');
require_once('../../../../include/studiensemester.class.php');
require_once('../../../../include/lehreinheit.class.php');
require_once('../../../../include/benutzerberechtigung.class.php');
require_once('../../../../include/uebung.class.php');
require_once('../../../../include/beispiel.class.php');
require_once('../../../../include/studentnote.class.php');
require_once('../../../../include/datum.class.php');
require_once('../../../../include/legesamtnote.class.php');
require_once('../../../../include/lvgesamtnote.class.php');
require_once('../../../../include/zeugnisnote.class.php');
require_once('../../../../include/person.class.php');
require_once('../../../../include/benutzer.class.php');
require_once('../../../../include/student.class.php');

if(!$conn = pg_pconnect(CONN_STRING))
	die('Fehler beim oeffnen der Datenbankverbindung');

$user = get_uid();

if(!check_lektor($user, $conn))
	die('Sie haben keine Berechtigung fuer diesen Bereich');

$rechte = new benutzerberechtigung($conn);
$rechte->getBerechtigungen($user);

if(isset($_GET['lvid']) && is_numeric($_GET['lvid'])) //Lehrveranstaltung_id
	$lvid = $_GET['lvid'];
else
	die('Fehlerhafte Parameteruebergabe');

//Laden der Lehrveranstaltung
$lv_obj = new lehrveranstaltung($conn);
if(!$lv_obj->load($lvid))
	die($lv_obj->errormsg);

//Studiengang laden
$stg_obj = new studiengang($conn,$lv_obj->studiengang_kz);

if(isset($_GET['stsem']))
	$stsem = $_GET['stsem'];
else
	$stsem = '';

//Vars
$datum_obj = new datum();
$response='';
$uebung_id = (isset($_GET['uebung_id'])?$_GET['uebung_id']:'');
$uid = (isset($_GET['uid'])?$_GET['uid']:'');

//Kopfzeile


//Studiensemester laden
$stsem_obj = new studiensemester($conn);
if($stsem=='')
	$stsem = $stsem_obj->getaktorNext();

//$note = $_REQUEST["note"];

if(!$rechte->isBerechtigt('admin',0) &&
   !$rechte->isBerechtigt('admin',$lv_obj->studiengang_kz) &&
   !$rechte->isBerechtigt('lehre',$lv_obj->studiengang_kz))
{
	$qry = "SELECT lehreinheit_id FROM lehre.tbl_lehrveranstaltung JOIN lehre.tbl_lehreinheit USING(lehrveranstaltung_id)
			JOIN lehre.tbl_lehreinheitmitarbeiter USING(lehreinheit_id) 
			WHERE tbl_lehrveranstaltung.lehrveranstaltung_id='".addslashes($lvid)."' AND
			tbl_lehreinheit.studiensemester_kurzbz='".addslashes($stsem)."' AND tbl_lehreinheitmitarbeiter.mitarbeiter_uid='".addslashes($user)."'";
	if($result = pg_query($conn, $qry))
	{
		if(pg_num_rows($result)==0)
			die('Sie haben keine Berechtigung für diese Seite');
	}
	else 
	{
		die('Fehler beim Pruefen der Rechte');
	}
}

function savenote($lvid, $student_uid, $note)
{
	global $conn, $stsem, $user;
	$jetzt = date("Y-m-d H:i:s");
	//Ermitteln ob der Student diesem Kurs zugeteilt ist
	$qry = "SELECT 1 FROM campus.vw_student_lehrveranstaltung WHERE uid='".addslashes($student_uid)."' AND lehrveranstaltung_id='".addslashes($lvid)."'";
	if($result = pg_query($conn, $qry))
		if(pg_num_rows($result)==0)
		{
			$student = new student($conn);
			$student->load($student_uid);
			die('Der Student '.$student->nachname.' '.$student->vorname.' ('.trim($student->matrikelnr).') ist dieser Lehrveranstaltung nicht zugeordnet. Die Note wird nicht uebernommen');
		}
	
	$lvgesamtnote = new lvgesamtnote($conn);
    if (!$lvgesamtnote->load($lvid, $student_uid, $stsem))
    {
		$lvgesamtnote->student_uid = $student_uid;
		$lvgesamtnote->lehrveranstaltung_id = $lvid;
		$lvgesamtnote->studiensemester_kurzbz = $stsem;
		$lvgesamtnote->note = $note;
		$lvgesamtnote->mitarbeiter_uid = $user;
		$lvgesamtnote->benotungsdatum = $jetzt;
		$lvgesamtnote->freigabedatum = null;
		$lvgesamtnote->freigabevon_uid = null;
		$lvgesamtnote->bemerkung = null;
		$lvgesamtnote->updateamum = null;
		$lvgesamtnote->updatevon = null;
		$lvgesamtnote->insertamum = $jetzt;
		$lvgesamtnote->insertvon = $user;
		$new = true;
		$response = "neu";
    }
    else
    {
		$lvgesamtnote->note = trim($note);
		$lvgesamtnote->benotungsdatum = $jetzt;
		$lvgesamtnote->updateamum = $jetzt;
		$lvgesamtnote->updatevon = $user;
		$new = false;
		if ($lvgesamtnote->freigabedatum)		
			$response = "update_f";
		else
			$response = "update";
	}
	if (!$lvgesamtnote->save($new))
		return "<span class='error'>".$lvgesamtnote->errormsg."</span>";
	else 
		return $response;
}

// lvgesamtnote für studenten speichern
if (isset($_REQUEST["submit"]))
{
	$lvid = $_REQUEST["lvid"];
	if(isset($_REQUEST["student_uid"]) && $_REQUEST["student_uid"] != '')
	{
		$student_uid = $_REQUEST["student_uid"];
		$note = $_REQUEST["note"];
		if((($note>0) && ($note < 6)) || ($note == 7) || ($note==8))
			$response = savenote($lvid, $student_uid, $note);
		else
			$response = "Bitte geben Sie eine Note von 1 - 5 bzw. 7 (nicht beurteilt) oder 8 (teilgenommen) ein!";
		
		echo $response;
	}
	else
	{
		foreach ($_POST as $row=>$val)
		{
			if(strstr($row, 'matrikelnr_'))
			{
				$id=substr($row, strlen('matrikelnr_'));
				if(isset($_POST['matrikelnr_'.$id]) && isset($_POST['note_'.$id]))
				{
					$matrikelnummer = $_POST['matrikelnr_'.$id];
					$note = $_POST['note_'.$id];
					
					//UID ermitteln
					$student = new student($conn);
					if(!$student_uid = $student->getUidFromMatrikelnummer($matrikelnummer))
					{
						$response.="\nStudent mit der Matrikelnummer ".$matrikelnummer.' existiert nicht';
						continue;
					}
					if((($note>0) && ($note < 6)) || ($note == 7) || ($note==8))
					{
						$val=savenote($lvid, $student_uid, $note);
						if($val!='neu' && $val!='update' && $val!='update_f')
							$response.=$val;
					}
					else
					{
						$student->load($student_uid);
						$response .= "\nFehlerhafte Note bei Student $student->nachname $student->vorname. Bitte geben Sie eine Note von 1 - 5 bzw. 7 (nicht beurteilt) oder 8 (teilgenommen) ein!";
					}
				}
				else 
				{
					$response.="\nFehler bei der Parameteruebergabe";					
				}
			}
		}
		echo $response;
	}	
}
?>
