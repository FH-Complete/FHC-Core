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
require_once('../../../../include/pruefungsanmeldung.class.php');
require_once('../../../../include/pruefungstermin.class.php');
require_once('../../../../include/datum.class.php');
require_once('../../../../include/konto.class.php');
require_once('../../../../include/student.class.php');
require_once('../../../../include/studiensemester.class.php');
require_once('../../../../include/lehreinheit.class.php');
require_once('../../../../include/studiengang.class.php');

$uid = get_uid();

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($uid);

$studiensemester = new studiensemester();
$aktStudiensemester = $studiensemester->getakt();

$method = isset($_REQUEST['method'])?$_REQUEST['method']:'';

switch($method)
{
	case 'getPruefungByLv':
	    $data = getPruefungByLv($aktStudiensemester, $uid);
            break;
	case 'getPruefungByLvFromStudiengang':
	    $data = getPruefungByLvFromStudiengang($aktStudiensemester, $uid);
            break;
        case 'loadPruefung':
            $data = loadPruefung();
            break;
        case 'loadTermine':
            $data = loadTermine();
            break;
        case 'saveAnmeldung':
            $data = saveAnmeldung($aktStudiensemester, $uid);
            break;
	case 'getAllPruefungen':
            $data = getAllPruefungen($aktStudiensemester, $uid);
	    break;
	case 'stornoAnmeldung':
	    $data = stornoAnmeldung($uid);
	    break;
//	case 'getPruefungMitarbeiter':
//	    $data = getPruefungMitarbeiter($uid);
//	    break;
	case 'getAnmeldungenTermin':
	    $data = getAnmeldungenTermin();
	    break;
	case 'saveReihung':
	    $data = saveReihung();
	    break;
	case 'anmeldungBestaetigen':
	    $data = anmeldungBestaetigen();
	    break;
	case 'getStudiengaenge':
	    $data = getStudiengaenge();
	    break;
	case 'getPruefungenStudiengang':
	    $data = getPruefungenStudiengang();
	    break;
	case 'saveKommentar':
	    $data = saveKommentar();
	    break;
	default:
	    break;
}

echo json_encode($data);

//Funktionen

/**
 * Lädt alle Prüfungen eines Studenten zu deren LVs er angemeldet ist
 * @param string $aktStudiensemester kurzbz des aktuellen Studiensemester (kann auch eine älteres sein)
 * @param string $uid UID des Studenten
 * @return Array
 */
function getPruefungByLv($aktStudiensemester = null, $uid = null)
{
    $lehrveranstaltungen = new lehrveranstaltung();
    $lehrveranstaltungen->load_lva_student($uid);
    $lvIds = array();
    foreach($lehrveranstaltungen->lehrveranstaltungen as $lvs)
    {
	array_push($lvIds, $lvs->lehrveranstaltung_id);
    }
    $lehrveranstaltungen=$lvIds;
    $pruefung = new pruefungCis();
    //TODO nur Prüfungen des aktuellen Studiensemesters
    if($pruefung->getPruefungByLv($lehrveranstaltungen))
    {
	$pruefungen = array();
	foreach($pruefung->lehrveranstaltungen as $key=>$lv)
	{
	    $lehrveranstaltung = new lehrveranstaltung($lv->lehrveranstaltung_id);
	    $lehrveranstaltung = $lehrveranstaltung->cleanResult();
	    $lehreinheit = new lehreinheit();
	    $lehreinheit->load_lehreinheiten($lehrveranstaltung[0]->lehrveranstaltung_id, $aktStudiensemester);
	    $lehreinheiten = $lehreinheit->lehreinheiten;
	    $prf = new stdClass();
	    $temp = new pruefungCis($lv->pruefung_id);
	    $temp->getTermineByPruefung($lv->pruefung_id);
	    for($i=0; $i < sizeof($temp->termine); $i++)
	    {
		$termin = new pruefungstermin($temp->termine[$i]->pruefungstermin_id);
		$temp->termine[$i]->teilnehmer = $termin->getNumberOfParticipants();
	    }
	    $prf->pruefung = $temp;
	    $prf->lehrveranstaltung = $lehrveranstaltung;
	    $lveranstaltung = new lehrveranstaltung($lehreinheiten[0]->lehrfach_id);
	    $oe = new organisationseinheit($lveranstaltung->oe_kurzbz);
	    $prf->organisationseinheit = $oe->bezeichnung;
	    array_push($pruefungen, $prf);
	}
	$anmeldung = new pruefungsanmeldung();
	$anmeldungen = $anmeldung->getAnmeldungenByStudent($uid, $aktStudiensemester);
	$anmeldungsIds = array();
	foreach($anmeldungen as $anm)
	{
	    $a = new stdClass();
	    $a->pruefungsanmeldung_id = $anm->pruefungsanmeldung_id;
	    $a->pruefungstermin_id = $anm->pruefungstermin_id;
	    $a->lehrveranstaltung_id = $anm->lehrveranstaltung_id;
	    array_push($anmeldungsIds, $a);
	}
	$return = new stdClass();
	$return->pruefungen = $pruefungen;
	$return->anmeldungen = $anmeldungsIds;
	$data['result']=$return;
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
 * Lädt alle Prüfungen die im Studiengang eines Studenten angeboten werden
 * @param string $aktStudiensemester kurzbz des aktuellen Studiensemester (kann auch eine älteres sein)
 * @param string $uid UID des Studenten
 * @return Array
 */
function getPruefungByLvFromStudiengang($aktStudiensemester = null, $uid = null)
{
    $lehrveranstaltungen = new lehrveranstaltung();
    $student = new student($uid);
    $lehrveranstaltungen->load_lva($student->studiengang_kz);
    $lvIds = array();
    foreach($lehrveranstaltungen->lehrveranstaltungen as $lvs)
    {
	array_push($lvIds, $lvs->lehrveranstaltung_id);
    }
    $lehrveranstaltungen=$lvIds;
    $pruefung = new pruefungCis();
    //TODO nur Prüfungen des aktuellen Studiensemesters
    if($pruefung->getPruefungByLv($lehrveranstaltungen))
    {
	$pruefungen = array();
	foreach($pruefung->lehrveranstaltungen as $lv)
	{
	    $lehrveranstaltung = new lehrveranstaltung($lv->lehrveranstaltung_id);
	    $lehrveranstaltung = $lehrveranstaltung->cleanResult();
	    $lehreinheit = new lehreinheit();
	    $lehreinheit->load_lehreinheiten($lehrveranstaltung[0]->lehrveranstaltung_id, $aktStudiensemester);
	    $lehreinheiten = $lehreinheit->lehreinheiten;
	    $prf = new stdClass();
	    $temp = new pruefungCis($lv->pruefung_id);
	    $temp->getTermineByPruefung($lv->pruefung_id);
	    for($i=0; $i < sizeof($temp->termine); $i++)
	    {
		$termin = new pruefungstermin($temp->termine[$i]->pruefungstermin_id);
		$temp->termine[$i]->teilnehmer = $termin->getNumberOfParticipants();
	    }
	    $prf->pruefung = $temp;
	    $prf->lehrveranstaltung = $lehrveranstaltung;
	    $lveranstaltung = new lehrveranstaltung($lehreinheiten[0]->lehrfach_id);
	    $oe = new organisationseinheit($lveranstaltung->oe_kurzbz);
	    $prf->organisationseinheit = $oe->bezeichnung;
	    array_push($pruefungen, $prf);
	}

	$anmeldung = new pruefungsanmeldung();
	$anmeldungen = $anmeldung->getAnmeldungenByStudent($uid, $aktStudiensemester);
	$anmeldungsIds = array();
	foreach($anmeldungen as $anm)
	{
	    $a = new stdClass();
	    $a->pruefungsanmeldung_id = $anm->pruefungsanmeldung_id;
	    $a->pruefungstermin_id = $anm->pruefungstermin_id;
	    $a->lehrveranstaltung_id = $anm->lehrveranstaltung_id;
	    array_push($anmeldungsIds, $a);
	}
	$return = new stdClass();
	$return->pruefungen = $pruefungen;
	$return->anmeldungen = $anmeldungsIds;
	$data['result']=$return;
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
 * Lädt die Daten zu einer einzelnen Prüfung
 * @return Array
 */
function loadPruefung()
{
    $pruefung_id=$_REQUEST["pruefung_id"];
    $pruefung = new pruefungCis();
    if($pruefung->load($pruefung_id))
    {
	$temp = array();
	$pruefung->getLehrveranstaltungenByPruefung();
	$pruefung->getTermineByPruefung();
	$studiengang = new studiengang();
	foreach($pruefung->lehrveranstaltungen as $lv)
	{
	    $lehrveranstaltung = new lehrveranstaltung($lv->lehrveranstaltung_id);
	    $lehrveranstaltung = $lehrveranstaltung->cleanResult();
	    $studiengang->load($lehrveranstaltung[0]->studiengang_kz);
	    $stg = new stdClass();
	    $stg->bezeichnung = $studiengang->bezeichnung;
	    $stg->studiengang_kz = $studiengang->studiengang_kz;
	    $stg->kurzbzlang = $studiengang->kurzbzlang;
	    $lehrveranstaltung[0]->studiengang = $stg;
	    $prf = new stdClass();
	    $prf->lehrveranstaltung = $lehrveranstaltung[0];
	    $prf->pruefung = $pruefung;
	    array_push($temp, $prf);
	}

	$data['result'] = array();
	$data['result'] = $temp;
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
 * Lädt die Termine zu einer Prüfung
 * @return Array
 */
function loadTermine()
{
    $pruefung_id=$_REQUEST["pruefung_id"];
    $pruefung = new pruefungCis($pruefung_id);
    if($pruefung->getTermineByPruefung($pruefung_id))
    {
	$data['result'] = $pruefung->termine;
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
 * speichert eine Prüfungsanmeldung
 * @param type $aktStudiensemester kurzbz des aktuellen Studiensemesters (wird für Berechnung auf ausreichend CreditPoints benötigt)
 * @param type $uid UID des Studenten
 * @return Array
 */
function saveAnmeldung($aktStudiensemester = null, $uid = null)
{
    $termin = new pruefungstermin($_REQUEST["termin_id"]);
    if($termin->teilnehmer_max > $termin->getNumberOfParticipants() || $termin->teilnehmer_max == NULL)
    {
	$pruefung = new pruefungCis();
	$reihung = $pruefung->getLastOfReihung($_REQUEST["termin_id"]);
	$anmeldung = new pruefungsanmeldung();
	$anmeldung->lehrveranstaltung_id = $_REQUEST["lehrveranstaltung_id"];
	$anmeldung->pruefungstermin_id = $_REQUEST["termin_id"];
	$anmeldung->wuensche = $_REQUEST["bemerkung"];
	$anmeldung->uid = $uid;
	$anmeldung->reihung = $reihung+1;
	$anmeldung->status_kurzbz = "angemeldet";

//                $studiensemester_kurbz = $_REQUEST['studiensemester_kurzbz'];

	$lehrveranstaltung = new lehrveranstaltung($_REQUEST["lehrveranstaltung_id"]);

	$konto = new konto();
	$creditpoints = $konto->getCreditPoints($uid, $aktStudiensemester);
//		$creditpoints = 1.5;
	if($creditpoints !== false)
	{
	    if($creditpoints < $lehrveranstaltung->ects)
	    {
	    $data['error'] = 'true';
	    $data['errormsg'] = 'Credit-Points-Guthaben ist zu gering.';
	    return $data;
	    }
	}

	//Kollisionsprüfung
	$anmeldungen = $anmeldung->getAnmeldungenByStudent($uid, $aktStudiensemester);
	foreach($anmeldungen as $temp)
	{
	    $datum = new datum();
	    if(($datum->between($termin->von, $termin->bis, $temp->von)) || ($datum->between($termin->von, $termin->bis, $temp->bis)))
	    {
		$data['result'][$temp->pruefungstermin_id] = "true";
		$data['error'] = 'true';
		$data['errormsg'] = 'Kollision mit anderer Anmeldung.';
	    }
	}
	if(isset($data['error']) && $data['error'] = 'true')
	{
	    return $data;
	}
    }
    else
    {
	$data['error']='true';
	$data['errormsg']='Keine freien Plätze vorhanden.';
	return $data;
    }

    if($anmeldung->save(true))
    {
	$data['result'] = "Anmeldung erfolgreich!";
	$data['error']='false';
	$data['errormsg']='';
    }
    else
    {
	$data['error']='true';
	$data['errormsg']=$anmeldung->errormsg;
    }
    return $data;
}

/**
 * Lädt alle vorhandenen Prüfungen
 * @param type $aktStudiensemester kurzbz des Studiensemesters (Filter nach Studiensemester)
 * @param type $uid UID eines Studenten
 * @return Array
 */
function getAllPruefungen($aktStudiensemester = null, $uid = null)
{
    $pruefung = new pruefungCis();
    //TODO Prüfungen des aktuellen Studiensemesters???
    if($pruefung->getAll())
    {
	$pruefungen = array();
	foreach($pruefung->lehrveranstaltungen as $lv)
	{
	    $lehrveranstaltung = new lehrveranstaltung($lv->lehrveranstaltung_id);
	    $lehrveranstaltung = $lehrveranstaltung->cleanResult();
	    $lehreinheit = new lehreinheit();
	    $lehreinheit->load_lehreinheiten($lehrveranstaltung[0]->lehrveranstaltung_id, $aktStudiensemester);
	    $lehreinheiten = $lehreinheit->lehreinheiten;
	    $prf = new stdClass();
	    $temp = new pruefungCis($lv->pruefung_id);
	    $temp->getTermineByPruefung($lv->pruefung_id);
	    for($i=0; $i < sizeof($temp->termine); $i++)
	    {
		$termin = new pruefungstermin($temp->termine[$i]->pruefungstermin_id);
		$temp->termine[$i]->teilnehmer = $termin->getNumberOfParticipants();
	    }
	    $prf->pruefung = $temp;
	    $prf->lehrveranstaltung = $lehrveranstaltung;
	    $lveranstaltung = new lehrveranstaltung($lehreinheiten[0]->lehrfach_id);
	    $oe = new organisationseinheit($lveranstaltung->oe_kurzbz);
	    $prf->organisationseinheit = $oe->bezeichnung;
	    array_push($pruefungen, $prf);
	}

	$anmeldung = new pruefungsanmeldung();
	$anmeldungen = $anmeldung->getAnmeldungenByStudent($uid, $aktStudiensemester);
	$anmeldungsIds = array();
	foreach($anmeldungen as $anm)
	{
	    $a = new stdClass();
	    $a->pruefungsanmeldung_id = $anm->pruefungsanmeldung_id;
	    $a->pruefungstermin_id = $anm->pruefungstermin_id;
	    $a->lehrveranstaltung_id = $anm->lehrveranstaltung_id;
	    array_push($anmeldungsIds, $a);
	}
	$return = new stdClass();
	$return->pruefungen = $pruefungen;
	$return->anmeldungen = $anmeldungsIds;
	$data['result']=$return;
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
 * Storniert eine Prüfungsanmeldung
 * @param type $uid UID eines Studenten
 * @return Array
 */
function stornoAnmeldung($uid = null)
{
    $pruefungsanmeldung_id=$_REQUEST['pruefungsanmeldung_id'];
    $pruefungsanmeldung = new pruefungsanmeldung();
    if($pruefungsanmeldung->delete($pruefungsanmeldung_id, $uid))
    {
	$data['result']='Anmeldung erfolgreich gelöscht.';
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
 * Lädt alle Prüfungen eines Lektors/Mitarbeiters
 * @param type $uid UID des Lektors/Mitarbeiters
 * @return Array
 */
//function getPruefungMitarbeiter($uid = null)
//{
//    $lehrveranstaltung = new lehrveranstaltung();
//    if($uid !== null)
//    {
//	$lehrveranstaltung->getLVByMitarbeiter($uid);
////	$lehrveranstaltung->getLVByMitarbeiter("neubauer");
//	$result = array();
//	foreach($lehrveranstaltung->lehrveranstaltungen as $lv)
//	{
//	    $pruefung = new pruefungCis();
//	    $pruefung->getPruefungByLv($lv->lehrveranstaltung_id);
//	    if($pruefung->lehrveranstaltungen[0]->pruefung_id !== null)
//	    {
//		$pruefung->load($pruefung->lehrveranstaltungen[0]->pruefung_id);
//		$pruefung->getTermineByPruefung();
//		$lv->pruefung = $pruefung;
//		array_push($result, $lv);
//	    }
//	}
//    }
//
//    if(!empty($result))
//    {
//	$data['result']=$result;
//	$data['error']='false';
//	$data['errormsg']='';
//    }
//    else
//    {
//	$data['error']='true';
//	$data['errormsg']="Keine Prüfungen vorhanden.";
//    }
//    return $data;
//}

/**
 * Lädt alle Anmeldungen zu einem Prüfungstermin
 * @return Array
 */
function getAnmeldungenTermin()
{
    $lehrveranstaltung_id = $_REQUEST["lehrveranstaltung_id"];
    $pruefungstermin_id = $_REQUEST["pruefungstermin_id"];
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

/**
 * speichert die Reihung der Studenten eines Prüfungstermines
 * @return Array
 */
function saveReihung()
{
    $anmeldung = new pruefungsanmeldung();
    $reihung = $_REQUEST["reihung"];
    if($anmeldung->saveReihung($reihung))
    {
	$data['result']=true;
	$data['error']='false';
	$data['errormsg']=$anmeldung->errormsg;
    }
    else
    {
	$data['error']='true';
	$data['errormsg']=$anmeldung->errormsg;
    }
    return $data;
}

/**
 * Ändert den Status einer Prüfungsanmeldung auf "bestaetigt"
 * @return Array
 */
function anmeldungBestaetigen()
{
    $pruefungsanmeldung_id = $_REQUEST["pruefungsanmeldung_id"];
    $status = "bestaetigt";
    $anmeldung = new pruefungsanmeldung();
    if($anmeldung->changeState($pruefungsanmeldung_id, $status))
    {
	$data['result']=true;
	$data['error']='false';
	$data['errormsg']='';
    }
    else
    {
	$data['error']='true';
	$data['errormsg']=$anmeldung->errormsg;
    }
    return $data;
}

/**
 * Lädt alle Studiengänge
 * @return Array
 */
function getStudiengaenge()
{
    $studiengang = new studiengang();
    if($studiengang->getAll("bezeichnung", true))
    {
	$result = array();
	foreach($studiengang->result as $stg)
	{
	    $studiengangTemp = new StdClass();
	    $studiengangTemp->studiengang_kz = $stg->studiengang_kz;
	    $studiengangTemp->bezeichnung = $stg->bezeichnung;
	    $studiengangTemp->kurzbz = $stg->kurzbz;
	    $studiengangTemp->typ = $stg->typ;
	    array_push($result, $studiengangTemp);
	}
	$data['result']=$result;
	$data['error']='false';
	$data['errormsg']='';
    }
    else
    {
	$data['error']='true';
	$data['errormsg']=$studiengang->errormsg;
    }
    return $data;
}

/**
 * Lädt alle Prüfungen eines Studienganges
 * @return Array
 */
function getPruefungenStudiengang()
{
    $lehrveranstaltung = new lehrveranstaltung();
    $lehrveranstaltung->load_lva($_REQUEST["studiengang_kz"], null, null, true, true);
    $result = array();
    foreach($lehrveranstaltung->lehrveranstaltungen as $lv)
    {
	$pruefung = new pruefungCis();
	$pruefung->getPruefungByLv($lv->lehrveranstaltung_id);
	if((!empty($pruefung->lehrveranstaltungen)))
	{
	    $lv->pruefung = array();
	    foreach ($pruefung->lehrveranstaltungen as $key=>$prf)
	    {
		$pruefung->load($prf->pruefung_id);
		if($pruefung->storniert === true)
		{
		    unset($pruefung->lehrveranstaltungen[$key]);
		}
		else
		{
		    $pruefung->getTermineByPruefung();
		    array_push($lv->pruefung, $pruefung);
		}
	    }
	    array_push($result, $lv);
	}
    }
    $data['result']=$result;
    $data['error']='false';
    $data['errormsg']='';
    return $data;
}

function saveKommentar()
{
    $kommentar = $_REQUEST["kommentar"];
    $pruefungsanmeldung_id = $_REQUEST["pruefungsanmeldung_id"];
    
    $pruefungsanmeldung = new pruefungsanmeldung($pruefungsanmeldung_id);
    $pruefungsanmeldung->kommentar = $kommentar;
    if($pruefungsanmeldung->save())
    {	
	$data['result']=true;
	$data['error']='false';
	$data['errormsg']='';
    }
    else
    {
	$data['error']='true';
	$data['errormsg']=$pruefungsanmeldung->errormsg;
    }
    return $data;
}
?>