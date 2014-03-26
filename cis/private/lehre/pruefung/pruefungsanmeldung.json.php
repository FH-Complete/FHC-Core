<?php
header( 'Expires:  -1' );
header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT' );
header( 'Cache-Control: no-store, no-cache, must-revalidate' );
header( 'Pragma: no-cache' );
header('Content-Type: text/html;charset=UTF-8');

require_once('../../../../config/vilesci.config.inc.php');
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

$uid = get_uid();

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($uid);

$studiensemester = new studiensemester();
$aktStudiensemester = $studiensemester->getakt();

$method = isset($_REQUEST['method'])?$_REQUEST['method']:'';

switch($method)
{
	case 'getPruefungByLv':
	    $lehrveranstaltungen = new lehrveranstaltung();
	    //TODO
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
            break;
	case 'getPruefungByLvFromStudiengang':
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
            break;
        case 'loadPruefung':
            $pruefung_id=$_REQUEST["pruefung_id"];
            $pruefung = new pruefungCis();
            if($pruefung->load($pruefung_id))
            {
		$temp = array();
		$pruefung->getLehrveranstaltungenByPruefung();
		foreach($pruefung->lehrveranstaltungen as $lv)
		{
		    $lehrveranstaltung = new lehrveranstaltung($lv->lehrveranstaltung_id);
		    $lehrveranstaltung = $lehrveranstaltung->cleanResult();
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
            
            break;
        case 'loadTermine':
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
            
            break;
        case 'saveAnmeldung':
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
                    break;
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
                    break;
                }
            }
            else
            {
                $data['error']='true';
                $data['errormsg']='Keine freien Plätze vorhanden.';
                break;
            }
            
            if($anmeldung->save(true))
//	    if(true)
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
            break;
	case 'getAllPruefungen':
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
			$temp =	new pruefungCis($lv->pruefung_id);
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
		    
//                    $data['result']=$pruefungen;
                    $data['error']='false';
                    $data['errormsg']='';
            }
            else
            {
                    $data['error']='true';
                    $data['errormsg']=$pruefung->errormsg;
            }
	    break;
	case 'stornoAnmeldung':
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
	    break;
	default:
		break;
}

echo json_encode($data);
?>