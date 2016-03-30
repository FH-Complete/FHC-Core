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
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>,
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at> and
 *          Andreas Moik <moik@technikum-wien.at>.
 */

require_once('../../../../config/cis.config.inc.php');
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

$user = get_uid();

if(!check_lektor($user))
	die('Sie haben keine Berechtigung fuer diesen Bereich');

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);

if(isset($_GET['lvid']) && is_numeric($_GET['lvid'])) //Lehrveranstaltung_id
	$lvid = $_GET['lvid'];
else
	die('Fehlerhafte Parameteruebergabe');

if(isset($_GET['lehreinheit_id']) && is_numeric($_GET['lehreinheit_id'])) //Lehreinheit_id
	$lehreinheit_id = $_GET['lehreinheit_id'];
else
	$lehreinheit_id = '';

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
$datum_obj = new datum();

$uebung_id = (isset($_GET['uebung_id'])?$_GET['uebung_id']:'');
$uid = (isset($_GET['uid'])?$_GET['uid']:'');

//Kopfzeile


//Studiensemester laden
$stsem_obj = new studiensemester();
if($stsem=='')
	$stsem = $stsem_obj->getaktorNext();




if($lehreinheit_id=='')
	die('Es wurde keine passende Lehreinheit in diesem Studiensemester gefunden');

$note = $_REQUEST["note"];

// lvgesamtnote für studenten speichern

if (isset($_REQUEST["submit"]) && ($_REQUEST["student_uid"] != '') && ((($note>0) && ($note < 6)) || ($note == 7) || ($note==8) || ($note==16))  ){
	
	$jetzt = date("Y-m-d H:i:s");	
	$student_uid = $_REQUEST["student_uid"];

	if(!$student = new student($user))
		die("Der Student wurde nicht gefunden!");

	$legesamtnote = new legesamtnote($lehreinheit_id);
	if (!$legesamtnote->load($student->prestudent_id,$lehreinheit_id))
	{
		$legesamtnote->prestudent_id = $prestudent_id;
		$legesamtnote->lehreinheit_id = $lehreinheit_id;
		$legesamtnote->note = $_REQUEST["note"];
		$legesamtnote->benotungsdatum = $jetzt;
		$legesamtnote->updateamum = null;
		$legesamtnote->updatevon = null;
		$legesamtnote->insertamum = $jetzt;
		$legesamtnote->insertvon = $user;
		$legesamtnote->new = true;
		$response = "neu";
    }
    else
    {
		$legesamtnote->note = $_REQUEST["note"];
		$legesamtnote->benotungsdatum = $jetzt;
		$legesamtnote->updateamum = $jetzt;
		$legesamtnote->updatevon = $user;
		$legesamtnote->new = false;
		$response = "update";
	}
	if (!$legesamtnote->save())
		echo "<span class='error'>".$legesamtnote->errormsg."</span>";
	else 
		echo $response;
}
else
	echo "Bitte geben Sie eine Note von 1 - 5 bzw. 7 (nicht beurteilt) oder 8 (teilgenommen) ein!";


?>
