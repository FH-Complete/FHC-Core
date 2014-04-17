<?php
header( 'Expires:  -1' );
header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT' );
header( 'Cache-Control: no-store, no-cache, must-revalidate' );
header( 'Pragma: no-cache' );
header('Content-Type: text/html;charset=UTF-8');

require_once('../../../../config/cis.config.inc.php');
require_once('../../../../include/functions.inc.php');
require_once('../../../../include/pruefungCis.class.php');
require_once('../../../../include/lehrveranstaltung.class.php');
require_once('../../../../include/benutzerberechtigung.class.php');
require_once('../../../../include/studiensemester.class.php');
require_once('../../../../include/note.class.php');
require_once('../../../../include/pruefung.class.php');
require_once('../../../../include/pruefungsanmeldung.class.php');
require_once('../../../../include/student.class.php');

$uid = get_uid();

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($uid);

$studiensemester = new studiensemester();
$aktStudiensemester = $studiensemester->getakt();

$method = isset($_REQUEST['method'])?$_REQUEST['method']:'';

switch($method)
{
	case 'getPruefungMitarbeiter':
	    if($rechte->isBerechtigt('lehre/pruefungsbeurteilungAdmin'))
	    {
		$mitarbeiter_uid = $_REQUEST["mitarbeiter_uid"];
	    }
	    else
	    {
		$mitarbeiter_uid = $uid;
	    }
	    $data = getPruefungMitarbeiter($mitarbeiter_uid);
	    break;
	case 'getNoten':
	    $data = getNoten();
	    break;
	case 'saveBeurteilung':
	    $lehrveranstaltung_id = $_REQUEST["lehrveranstaltung_id"];
	    $student_uid = $_REQUEST["student_uid"];
	    if($rechte->isBerechtigt('lehre/pruefungsbeurteilungAdmin'))
	    {
		$mitarbeiter_uid = $_REQUEST["mitarbeiter_uid"];
	    }
	    else
	    {
		$mitarbeiter_uid = $uid;
	    }
	    $note = $_REQUEST["note"];
	    $pruefung_id = $_REQUEST["pruefung_id"];
	    $datum = $_REQUEST["datum"];
	    $anmerkung = $_REQUEST["anmerkung"];
	    $pruefungsanmeldung_id = $_REQUEST["pruefungsanmeldung_id"];
	    $data = saveBeurteilung($lehrveranstaltung_id, $student_uid, $mitarbeiter_uid, $note, $pruefung_id, $datum, $anmerkung, $pruefungsanmeldung_id, $uid);
	    break;
	case 'updateBeurteilung':
	    $pruefung_id = $_REQUEST["pruefung_id"];
	    $note = $_REQUEST["note"];
	    $data = updateBeurteilung($pruefung_id, $note, $uid);
	    break;
	case 'loadPruefung':
	    $pruefung_id=$_REQUEST["pruefung_id"];
	    $data = loadPruefung($pruefung_id);
	    break;
	case 'getBeurteilung':
	    $pruefungsanmeldung_id = $_REQUEST["pruefungsanmeldung_id"];
	    $data = getBeurteilung($pruefungsanmeldung_id);
	    break;
	case 'getAnmeldungenTermin':
	    $lehrveranstaltung_id = $_REQUEST["lehrveranstaltung_id"];
	    $pruefungstermin_id = $_REQUEST["pruefungstermin_id"];
	    $data = getAnmeldungenTermin($lehrveranstaltung_id, $pruefungstermin_id);
	    break;
	default:
	    break;
}

echo json_encode($data);

/**
 * Lädt alle Prüfungen eines Lektors/Mitarbeiters
 * @param type $uid UID des Lektors/Mitarbeiters
 * @return Array
 */
function getPruefungMitarbeiter($uid = null)
{   
    $lehrveranstaltung = new lehrveranstaltung();
    if($uid !== null)
    {
	$lehrveranstaltung->getLVByMitarbeiter($uid);
	$result = array();
	foreach($lehrveranstaltung->lehrveranstaltungen as $lv)
	{
	    $pruefung = new pruefungCis();
	    $pruefung->getPruefungByLv($lv->lehrveranstaltung_id);
	    if(!empty($pruefung->lehrveranstaltungen))
	    {
		foreach($pruefung->lehrveranstaltungen as $tempLv)
		{
		    $prf = new pruefungCis($tempLv->pruefung_id);
		    $prf->getTermineByPruefung();
		    $tempLv->pruefung = $prf;
		}
		$lv->pruefung = $pruefung;
		array_push($result, $lv);
	    }
	}
    }

    if(!empty($result))
    {
	$data['result']=$result;
	$data['error']='false';
	$data['errormsg']='';
    }
    else
    {
	$data['error']='true';
	$data['errormsg']="Keine Prüfungen vorhanden.";
    }
    return $data;
}

/**
 * Lädt alle Noten per AJAX aus der Datenbank
 * @return Array
 */
function getNoten()
{
    $note = new note();
    if($note->getAll())
    {
	$data['result']=$note->result;
	$data['error']='false';
	$data['errormsg']='';
    }
    else
    {
	$data['error']='true';
	$data['errormsg']=$note->errormsg;
    }
    return $data;
}

/**
 * Speichert eine Beurteilung
 * @param int $lehrveranstaltung_id ID der Lehrveranstaltung
 * @param String $student_uid UID des Studenten
 * @param String $mitarbeiter_uid UID des Lektors
 * @param int $note Prüfungsnote
 * @param int $pruefung_id ID der Prüfung
 * @param String $datum Datum (YYYY-MM-DD)
 * @param String $anmerkung Anmerkung zur Beurteilung
 * @param int $pruefungsanmeldung_id ID der Anmeldung
 * @param String $uid UID des aktuellen Benutzers
 * @return Arrray
 */
function saveBeurteilung($lehrveranstaltung_id, $student_uid, $mitarbeiter_uid, $note, $pruefung_id, $datum, $anmerkung, $pruefungsanmeldung_id, $uid)
{
    $pruefungCis = new pruefungCis($pruefung_id);
    $lehrveranstaltung = new lehrveranstaltung();
    $lehreinheiten = $lehrveranstaltung->getLehreinheitenOfLv($lehrveranstaltung_id, $student_uid);
    $pruefung = new pruefung();
    $pruefung->new = true;
    $pruefung->lehreinheit_id = $lehreinheiten[0];
    $pruefung->student_uid = $student_uid;
    $pruefung->mitarbeiter_uid = $mitarbeiter_uid;
    $pruefung->note = $note;
    $pruefung->pruefungstyp_kurzbz = $pruefungCis->pruefungstyp_kurzbz;
    $pruefung->datum = $datum;
    $pruefung->anmerkung = $anmerkung;
    $pruefung->pruefungsanmeldung_id = $pruefungsanmeldung_id;
    $pruefung->insertvon = $uid;
    $pruefung->insertamum = date('Y-m-d H:i:s');
    if($pruefung->save())
    {
	$data['result']=$pruefung->pruefung_id;
	$data['error']='false';
	$data['errormsg']='';
    }
    else
    {
	$data['error']='true';
	$data['errormsg']=$pruefung->errormsg;
    }
    return $data;
}

/**
 * Aktualisiert den Datensatz einer Beurteilung
 * @param int $pruefung_id ID der Prüfung
 * @param int $note Prüfungsnote
 * @param String $uid UID des aktuellen Benutzers
 * @return Array
 */
function updateBeurteilung($pruefung_id, $note, $uid)
{
    $pruefung = new pruefung($pruefung_id);
    $pruefung->new = FALSE;
    $pruefung->note = $note;
    $pruefung->updatevon = $uid;
    $pruefung->updateamum = date('Y-m-d H:i:s');
    if($pruefung->save())
    {
	$data['result']=$pruefung->pruefung_id;
	$data['error']='false';
	$data['errormsg']='';
    }
    else
    {
	$data['error']='true';
	$data['errormsg']=$pruefung->errormsg;
    }
    return $data;
}

/**
 * Lädt die Beurteilung zu einer Anmeldung
 * @param int $pruefungsanmeldung_id ID einer Anmeldung
 * @return Array
 */
function getBeurteilung($pruefungsanmeldung_id)
{
    $pruefung = new pruefung();
    if($pruefung->getPruefungByAnmeldung($pruefungsanmeldung_id))
    {
	$data['result']=$pruefung->pruefung_id;
	$data['error']='false';
	$data['errormsg']='';
    }
    else
    {
	$data['error']='true';
	$data['errormsg']=$pruefung->errormsg;
    }
    return $data;
}

/**
 * Lädt alle Anmeldungen zu einem Prüfungstermin
 * @return Array
 */
function getAnmeldungenTermin($lehrveranstaltung_id, $pruefungstermin_id)
{
    $pruefungsanmeldung = new pruefungsanmeldung();
    $anmeldungen = $pruefungsanmeldung->getAnmeldungenByTermin($pruefungstermin_id, $lehrveranstaltung_id);
    foreach($anmeldungen as $a)
    {
	$student = new student($a->uid);
	$temp = new stdClass();
	$temp->vorname = $student->vorname;
	$temp->nachname = $student->nachname;
	$temp->uid = $student->uid;
	$a->student = $temp;
	$pruefung = new pruefung();
	$pruefung->getPruefungByAnmeldung($a->pruefungsanmeldung_id);
	$a->pruefung = $pruefung;
    }
    if(!empty($anmeldungen))
    {
	$data['result']=$anmeldungen;
	$data['error']='false';
	$data['errormsg']='';
    }
    else
    {
	$data['error']='true';
	if($pruefungsanmeldung->errormsg !== null)
	{
	    $data['errormsg']=$pruefungsanmeldung->errormsg;
	}
	else
	{
	    $data['errormsg']= 'Keine Anmeldungen vorhanden';
	}
    }
    return $data;
}