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
 *          Rudolf Hangl 		< rudolf.hangl@technikum-wien.at >
 *          Gerald Simane-Sequens 	< gerald.simane-sequens@technikum-wien.at >
 */

require_once('../../../../config/cis.config.inc.php');
require_once('../../../../include/basis_db.class.php');	
require_once('../../../../include/functions.inc.php');
require_once('../../../../include/lehrveranstaltung.class.php');
require_once('../../../../include/studiengang.class.php');
require_once('../../../../include/studiensemester.class.php');
require_once('../../../../include/lehreinheit.class.php');
require_once('../../../../include/uebung.class.php');
require_once('../../../../include/beispiel.class.php');
require_once('../../../../include/studentnote.class.php');
require_once('../../../../include/datum.class.php');
require_once('../../../../include/legesamtnote.class.php');
require_once('../../../../include/lvgesamtnote.class.php');
require_once('../../../../include/zeugnisnote.class.php');
require_once('../../../../include/pruefung.class.php');
require_once('../../../../include/mail.class.php');
require_once('../../../../include/benutzerfunktion.class.php');
require_once('../../../../include/benutzer.class.php');
require_once('../../../../include/student.class.php');

if (!$db = new basis_db())
	die('Fehler beim Herstellen der Datenbankverbindung');

$user = get_uid();

if(!check_lektor($user))
	die('Sie haben keine Berechtigung fuer diesen Bereich');


$lehreinheit_id='';

if(isset($_GET['lvid']) && is_numeric($_GET['lvid'])) //Lehrveranstaltung_id
	$lvid = $_GET['lvid'];
else
	die('Fehlerhafte Parameteruebergabe');

if(isset($_GET['lehreinheit_id']) && is_numeric($_GET['lehreinheit_id'])) //Lehreinheit_id
	$lehreinheit_id = $_GET['lehreinheit_id'];
//else
//	die('Fehlerhafte Parameteruebergabe');

if(isset($_GET['lehreinheit_id_pr']) && is_numeric($_GET['lehreinheit_id_pr'])) //Lehreinheit_id der pruefung
	$lehreinheit_id = $_GET['lehreinheit_id_pr'];
	
if(isset($_GET['datum']))
{
	$datum = $_GET['datum'];
	$datum_obj = new datum();
	$datum = $datum_obj->checkformatDatum($datum, 'Y-m-d', true) OR die('Invalid date format');
}
else
	die('Fehlerhafte Parameteruebergabe');

//Laden der Lehrveranstaltung
$lv_obj = new lehrveranstaltung();
if(!$lv_obj->load($lvid))
	die($lv_obj->errormsg);

//Studiengang laden
$stg_obj = new studiengang($lv_obj->studiengang_kz);

if(isset($_GET['stsem']))
	$stsem = $_GET['stsem'];
else
	$stsem = '';
	
//Vars

$uebung_id = (isset($_GET['uebung_id'])?$_GET['uebung_id']:'');
$uid = (isset($_GET['uid'])?$_GET['uid']:'');

//Kopfzeile


//Studiensemester laden
$stsem_obj = new studiensemester();
if($stsem=='')
	$stsem = $stsem_obj->getaktorNext();

$student_uid = $_REQUEST["student_uid"];


//if($lehreinheit_id=='')
//	die('Es wurde keine passende Lehreinheit in diesem Studiensemester gefunden');

$note = $_REQUEST["note"];
if ( (($note>0) && ($note < 6)) || ($note == 7) || ($note==16) || ($note==10) || ($note==14))
	$note = $_REQUEST["note"];
else
	$note = 9;

$old_note = $note;

// lvgesamtnote für studenten speichern
if (isset($_REQUEST["submit"]) && ($_REQUEST["student_uid"] != '')  ){
	
		// lehreinheiten holen, in denen der student ist	
		$le_arr = array();			
		$qry_stud = "SELECT DISTINCT lehreinheit_id, lehrform_kurzbz FROM campus.vw_student_lehrveranstaltung JOIN campus.vw_student using(uid) WHERE  studiensemester_kurzbz = '".$stsem."' and lehrveranstaltung_id = '".$lvid."' and uid='".$student_uid."' ORDER BY lehrform_kurzbz DESC";
		 if($result_stud = $db->db_query($qry_stud))
			{
				$i=1;
				while($row_stud = $db->db_fetch_object($result_stud))
				{
					$le_arr[] = $row_stud->lehreinheit_id;
				}
			}

		if (!in_array($lehreinheit_id,$le_arr))
			$lehreinheit_id = $le_arr[0];
	
	$jetzt = date("Y-m-d H:i:s");

	$pr = new Pruefung();

	if($pr->getPruefungen($student_uid, "Termin1", $lvid, $stsem))
	{
		if ($pr->result)
			$termin1 = 1;
		else
			{
				$lvnote = new lvgesamtnote();
				if ($lvnote->load($lvid, $student_uid, $stsem))
				{
					$pr_note = $lvnote->note;
					$benotungsdatum = $lvnote->benotungsdatum;
				}
				else
				{
					$pr_note = 9;
					$benotungsdatum = $jetzt;
				}
				$pr_1 = new Pruefung();
				$pr_1->lehreinheit_id = $lehreinheit_id;
				$pr_1->student_uid = $student_uid;
				$pr_1->mitarbeiter_uid = $user;
				$pr_1->note = $pr_note;
				$pr_1->pruefungstyp_kurzbz = "Termin1";
				$pr_1->datum = $benotungsdatum;
				$pr_1->anmerkung = "";
				$pr_1->insertamum = $jetzt;
				$pr_1->insertvon = $user;
				$pr_1->updateamum = Null;
				$pr_1->updatevon = Null;
				$pr_1->ext_id = Null;
				$pr_1->new = 1;
				$pr_1->save();
			}
	}

	$prTermin2 = new Pruefung();
	$pr_2 = new Pruefung();

	if ($prTermin2->getPruefungen($student_uid, "Termin2", $lvid, $stsem))
	{
		if	($prTermin2->result)
		{
			$pr_2->load($prTermin2->result[0]->pruefung_id);		
			$pr_2->new = null;
			$pr_2->updateamum = $jetzt;
			$pr_2->updatevon = $user;
			$old_note = $pr_2->note;
			$pr_2->note = $note;
			$pr_2->datum = $datum;
			$pr_2->anmerkung = "";
		}
		else
		{
			$pr_2->lehreinheit_id = $lehreinheit_id;
			$pr_2->student_uid = $student_uid;
			$pr_2->mitarbeiter_uid = $user;
			$pr_2->note = $note;
			$pr_2->pruefungstyp_kurzbz = "Termin2";
			$pr_2->datum = $datum;
			$pr_2->anmerkung = "";
			$pr_2->insertamum = $jetzt;
			$pr_2->insertvon = $user;
			$pr_2->updateamum = Null;
			$pr_2->updatevon = Null;
			$pr_2->ext_id = Null;
			$pr_2->new = 1;
			$old_note = -1;		
		}
		$pr_2->save();
	}


	if ( (($note>0) && ($note < 6)) || ($note == 7) || ($note==16) || ($note==10) || ($note==14))
	{

		$jetzt = date("Y-m-d H:i:s");	
	
		$lvid = $_REQUEST["lvid"];
		$lvgesamtnote = new lvgesamtnote();
	    if (!$lvgesamtnote->load($lvid, $student_uid, $stsem))
	    {
			$lvgesamtnote->student_uid = $student_uid;
			$lvgesamtnote->lehrveranstaltung_id = $lvid;
			$lvgesamtnote->studiensemester_kurzbz = $stsem;
			$lvgesamtnote->note = $_REQUEST["note"];
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
			$lvgesamtnote->note = $_REQUEST["note"];
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
			echo "<span class='error'>".$lvgesamtnote->errormsg."</span>";
		else 
			echo $response;
	}
	else
		echo "update_pr";
}
else
	echo "Fehler beim Eintragen der Pr&uuml;fungen";

/*
Beim Eintragen von Nachpruefungen wird ein Mail an die Assistenz geschickt.

17.06.2010 auf Wunsch der Assistenz werden keine Mails mehr versendet

if ($old_note != $note)
{
	    $qry = "SELECT distinct on(uid) vorname, nachname, tbl_benutzer.uid as uid, oe_kurzbz 
	    		FROM lehre.tbl_lehreinheit
	    		JOIN lehre.tbl_lehreinheitmitarbeiter USING (lehreinheit_id)
	    		JOIN public.tbl_benutzer ON (uid=mitarbeiter_uid)
	    		JOIN public.tbl_person USING (person_id)
	    		JOIN lehre.tbl_lehrveranstaltung USING (lehrveranstaltung_id)
	    		JOIN public.tbl_studiengang USING (studiengang_kz)
	    		WHERE 
	    			lehrveranstaltung_id='$lvid' AND 
	    			tbl_lehreinheitmitarbeiter.mitarbeiter_uid NOT like '_Dummy%' AND 
	    			tbl_benutzer.aktiv=true AND tbl_person.aktiv=true AND 
	    			studiensemester_kurzbz='$stsem'";

    $mailto = '';
    if(($result = $db->db_query($qry)) != FALSE)
    {
        if ($db->db_num_rows($result) > 0)
        {
            $row_lector = $db->db_fetch_object($result);
            $oe_kurzbz = $row_lector->oe_kurzbz;
            $mailto = $row_lector->uid.'@'.DOMAIN;
            while (($row_lector = $db->db_fetch_object($result)) != FALSE)
                $mailto .= ','.$row_lector->uid.'@'.DOMAIN;
        }        
    }
    
    $ass = new benutzerfunktion();
    if ($ass->getBenutzerFunktionen("ass",$oe_kurzbz))
        foreach ($ass->result as $res) $mailto .= (empty($mailto) ? "" : ",").$res->uid.'@'.DOMAIN;
        
    if($mailto != '')
    {
        $culprit = new benutzer($user);
        $victim = new student($student_uid);

        $mail = new mail(	$mailto,
        					'CIS-System@do.not.reply',
        					'[CIS-System] Note der Nachprüfung wurde '.($old_note < 0 ? 'eingetragen' : 'geändert'),
        					"Automatische Benachrichtigung:\n\nDie Nachprüfungsnote von\n\n$victim->vorname $victim->nachname ($student_uid)\n\nwurde im Studiensemester $stsem von $culprit->vorname $culprit->nachname ($user)\n\n".
                            ($old_note < 0
                            ? "mit '$note' eingetragen."
                            : "von '$old_note' auf '$note' geändert.").
                            "\n");
        if (!$mail->send())
        {
            sleep(3);
            $mail->send();    // Desperate second attempt. 
        }
    }
}
*/
?>
