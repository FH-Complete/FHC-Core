<?php
header( 'Expires:  -1' );
header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT' );
header( 'Cache-Control: no-store, no-cache, must-revalidate' );
header( 'Pragma: no-cache' );
header('Content-Type: text/html;charset=UTF-8');

require_once('../../../../config/cis.config.inc.php');
require_once('../../../../config/global.config.inc.php');
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
require_once('../../../../include/ort.class.php');
require_once('../../../../include/stunde.class.php');
require_once('../../../../include/reservierung.class.php');
require_once('../../../../include/mitarbeiter.class.php');
require_once('../../../../include/pruefung.class.php');
require_once('../../../../include/pruefungsfenster.class.php');
require_once('../../../../include/note.class.php');
require_once('../../../../include/addon.class.php');
require_once('../../../../include/mail.class.php');
require_once('../../../../include/anrechnung.class.php');
require_once('../../../../include/prestudent.class.php');
require_once('../../../../include/person.class.php');
require_once('../../../../include/phrasen.class.php');
require_once('../../../../include/globals.inc.php');
require_once('../../../../include/sprache.class.php');

$sprache = getSprache();
$lang = new sprache();
$lang->load($sprache);
$p = new phrasen($sprache);

$uid = get_uid();

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($uid);

$studiensemester = new studiensemester();
$aktStudiensemester = $studiensemester->getaktorNext();

$method = isset($_REQUEST['method'])?$_REQUEST['method']:'';

switch($method)
{
	case 'getPruefungByLv':
		$studiensemester = isset($_REQUEST['studiensemester']) && $_REQUEST['studiensemester'] != '0' ? $_REQUEST['studiensemester'] : NULL;
		$data = getPruefungByLv($studiensemester, $uid);
			break;
	case 'getPruefungByLvFromStudiengang':
		$studiensemester = isset($_REQUEST['studiensemester']) ? $_REQUEST['studiensemester'] : NULL;
		$data = getPruefungByLvFromStudiengang($studiensemester, $uid);
			break;
		case 'loadPruefung':
			$data = loadPruefung();
			break;
		case 'loadTermine':
			$data = loadTermine();
			break;
		case 'saveAnmeldung':
			$student_uid = filter_input(INPUT_POST,"uid");
			if($student_uid !== "" && !is_null($student_uid))
			{
				$uid = $student_uid;
			}

			if($student_uid === "")
			{
				$data['result']="";
				$data['error']='true';
				$data['errormsg']='Studenten UID fehlt.';
				break;
			}
			$data = saveAnmeldung($aktStudiensemester, $uid);
			break;
	case 'getAllPruefungen':
			$data = getAllPruefungen($aktStudiensemester, $uid);
		break;
	case 'stornoAnmeldung':
		$data = stornoAnmeldung($uid);
		break;
	case 'getAnmeldungenTermin':
		$data = getAnmeldungenTermin();
		break;
	case 'saveReihung':
		$data = saveReihung();
		break;
	case 'anmeldungBestaetigen':
		$data = anmeldungBestaetigen($uid);
		break;
	case 'anmeldungLoeschen':
		$data = anmeldungLoeschen();
		break;
	case 'alleBestaetigen':
		$data = alleBestaetigen($uid);
		break;
	case 'getStudiengaenge':
		$data = getStudiengaenge();
		break;
	case 'getPruefungenStudiengang':
		$studiensemester = filter_input(INPUT_POST,"studiensemester");
		$data = getPruefungenStudiengang($uid, $studiensemester);
		break;
	case 'saveKommentar':
		$data = saveKommentar();
		break;
	case 'getAllFreieRaeume':
		$terminId = $_REQUEST["terminId"];
		$data = getAllFreieRaeume($terminId);
		break;
	case 'saveRaum':
		$terminId = $_REQUEST["terminId"];
		$ort_kurzbz = $_REQUEST["ort_kurzbz"];
		$data = saveRaum($terminId, $ort_kurzbz, $uid);
		break;
	case 'getLvKompatibel':
		$lvid = filter_input(INPUT_POST, "lehrveranstaltung_id");
		$data = getLvKompatibel($lvid, $uid);
		break;
	case 'getPrestudenten':
		$data = getPrestudenten($uid, $aktStudiensemester);
		break;
	default:
		$data['error']='true';
		$data['errormsg']="unknown method: ".$method;
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
	$lehrveranstaltungen->load_lva_student($uid, $aktStudiensemester);
	$lvIds = array();
	foreach($lehrveranstaltungen->lehrveranstaltungen as $lvs)
	{
		array_push($lvIds, $lvs->lehrveranstaltung_id);
	}
	$lehrveranstaltungen=$lvIds;
	$pruefung = new pruefungCis();
	if($pruefung->getPruefungByLv($lehrveranstaltungen, $uid))
	{
		$pruefungen = array();
		foreach($pruefung->lehrveranstaltungen as $key=>$lv)
		{
			$lehrveranstaltung = new lehrveranstaltung($lv->lehrveranstaltung_id);
			$lehrveranstaltung = $lehrveranstaltung->cleanResult();
			$lehreinheit = new lehreinheit();
			if ($aktStudiensemester == null)
				$lehreinheit->load_all_lehreinheiten($lehrveranstaltung[0]->lehrveranstaltung_id);
			else
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
			if(!empty($lehreinheiten))
			{
			$lveranstaltung = new lehrveranstaltung($lehreinheiten[0]->lehrfach_id);
			$oe = new organisationseinheit($lveranstaltung->oe_kurzbz);
			$prf->organisationseinheit = $oe->bezeichnung;

			// nur hinzufügen wenn zumindest 1 Termin vorhanden ist
			if (!empty($prf->pruefung->termine))
				array_push($pruefungen, $prf);
			}
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
	$lv_angemeldet = new lehrveranstaltung();
	$lv_angemeldet->load_lva_student($uid, $aktStudiensemester);
	$lvIds_angemeldet = array();
	foreach($lv_angemeldet->lehrveranstaltungen as $lv)
	{
	array_push($lvIds_angemeldet, $lv->lehrveranstaltung_id);
	}
	$student = new student($uid);
	$lehrveranstaltungen->load_lva($student->studiengang_kz);
	$lvIds = array();
	foreach($lehrveranstaltungen->lehrveranstaltungen as $lvs)
	{
	array_push($lvIds, $lvs->lehrveranstaltung_id);
	}
	$lehrveranstaltungen=$lvIds;
	$pruefung = new pruefungCis();
	if($pruefung->getPruefungByLv($lehrveranstaltungen))
	{
	$pruefungen = array();
	foreach($pruefung->lehrveranstaltungen as $key=>$lv)
	{
		$lehrveranstaltung = new lehrveranstaltung($lv->lehrveranstaltung_id);
		$lehrveranstaltung = $lehrveranstaltung->cleanResult();
		if(in_array($lehrveranstaltung[0]->lehrveranstaltung_id, $lvIds_angemeldet))
		{
		$lehrveranstaltung[0]->angemeldet = true;
		}
		else
		{
		$lehrveranstaltung[0]->angemeldet = false;
		}
		$lehreinheit = new lehreinheit();
		$lehreinheit->load_lehreinheiten($lehrveranstaltung[0]->lehrveranstaltung_id, $aktStudiensemester);
		$lehreinheiten = $lehreinheit->lehreinheiten;
		if(!empty($lehreinheiten) && $lehreinheiten !== null)
		{
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

		// nur hinzufügen wenn zumindest 1 Termin vorhanden ist
		if (!empty($prf->pruefung->termine))
			array_push($pruefungen, $prf);
		}
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
	if(!empty($pruefung->lehrveranstaltungen))
	{
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
	}
	else
	{
		$prf = new stdClass();
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
	global $p;
	$termin = new pruefungstermin($_REQUEST["termin_id"]);
	$pruefung = new pruefung();
	$lehrveranstaltung = new lehrveranstaltung($_REQUEST["lehrveranstaltung_id"]);
	$studiensemester = new studiensemester();
	$stdsem = $studiensemester->getLastOrAktSemester(0);
	$lv_besucht = false;
	$studienverpflichtung_id = filter_input(INPUT_POST, "studienverpflichtung_id");
	$studiengang_kz = filter_input(INPUT_POST, "studiengang_kz");

	//Defaulteinstellung für Anzahlprüfungsversuche (wird durch Addon "ktu" überschrieben)
	$maxAnzahlVersuche = 0;

	//Defaulteinstellung für Code Note "unetnschuldigt ferngeblieben" (wird durch Addon "ktu" überschrieben)
	$noteCode_uef = -1;

	$addon = new addon();
	foreach ($addon->aktive_addons as $a)
	{
		if($a === "ku")
		{
			require '../../../../addons/'.$a.'/cis/prfVerwaltung_array.php';
			switch($lehrveranstaltung->oe_kurzbz)
			{
			case $fakultaeten[0]["fakultaet"]:
				$semCounter = $fakultaeten[0]["sem"];
				break;
			case $fakultaeten[1]["fakultaet"]:
				$semCounter = $fakultaeten[1]["sem"];
				break;
			default:
				$semCounter = 3;
				break;
			}
		}
		else
		{
			$semCounter = 99;
		}
	}
	$i=0;
	$stdsem_lv_besuch = null;
	do
	{
		$lehrveranstaltung->load_lva_student($uid, $stdsem);
		foreach($lehrveranstaltung->lehrveranstaltungen as $lv)
		{
			if($lv->lehrveranstaltung_id === $lehrveranstaltung->lehrveranstaltung_id)
			{
				$lv_besucht = true;
				$stdsem_lv_besuch = $stdsem;
			}
		}

		$stdsem = $studiensemester->getPreviousFrom($stdsem);
		$lehrveranstaltung->lehrveranstaltungen = array();
		$i++;
	}
	while($i<=$semCounter && $lv_besucht === FALSE);

	if(!$lv_besucht)
	{
		$data['error']='true';
		$data['errormsg']='Besuch der Lehrveranstaltung liegt zu weit in der Vergangenheit.';
		return $data;
	}

	$pruefung->getPruefungen($uid, NULL, $lehrveranstaltung->lehrveranstaltung_id);
	$anmeldung_moeglich = true;
	$anzahlPruefungen = count($pruefung->result);

	// Defaulteinstellung für Prüfungstypen - schauen, ob bereits aus KTU-Addon geladen
	if(!isset($pruefungstyp_kurzbzArray))
		$pruefungstyp_kurzbzArray = array("Termin1","Termin2","kommPruef");

	if(isset($pruefungstyp_kurzbzArray))
	{
		if($anzahlPruefungen < count($pruefungstyp_kurzbzArray))
		{
			$pruefungstyp_kurzbz = $pruefungstyp_kurzbzArray[$anzahlPruefungen];
		}
	}
	else
	{
		$pruefungstyp_kurzbz = null;
	}

	foreach($pruefung->result as $prf)
	{
		$note = new note($prf->note);
		if($note->note === $noteCode_uef)
		{
			$pruefungsanmeldung = new pruefungsanmeldung($prf->pruefungsanmeldung_id);
			$pruefungstermin = new pruefungstermin($pruefungsanmeldung->pruefungstermin_id);
			$pf = new pruefungCis($pruefungstermin->pruefung_id);
			$pruefungsfenster = new pruefungsfenster($pf->pruefungsfenster_id);
			$studiensemester = new studiensemester();
			$stdsem = $studiensemester->getaktorNext();
			$i=0;
			while($i<2)
			{
				if($stdsem === $pruefungsfenster->studiensemester_kurzbz)
				{
					$anmeldung_moeglich = false;
				}
				$stdsem = $studiensemester->getPreviousFrom($stdsem);
				$i++;
			}
		}
		else
		{
			if($note->positiv === FALSE && $anzahlPruefungen >= $maxAnzahlVersuche)
			{
				$anmeldung_moeglich = false;
			}
		}
	}

	if($anmeldung_moeglich)
	{
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
			$anmeldung->pruefungstyp_kurzbz = $pruefungstyp_kurzbz;
			$lehrveranstaltung = new lehrveranstaltung($_REQUEST["lehrveranstaltung_id"]);

//			$konto = new konto();
//			$creditpoints = $konto->getCreditPoints($uid, $aktStudiensemester);
//
//			if($creditpoints !== false)
//			{
//				if($creditpoints <= $lehrveranstaltung->ects)
//				{
//					$data['error'] = 'true';
//					$data['errormsg'] = $p->t('pruefung/zuWenigeCreditPoints');
//					return $data;
//				}
//			}

			//Kollisionsprüfung und Prüfung auf ausreichen Creditpoints
			$pruefungstermin = new pruefungstermin($_REQUEST["termin_id"]);
			$pf = new pruefungCis($pruefungstermin->pruefung_id);
			$pruefungsfenster = new pruefungsfenster($pf->pruefungsfenster_id);
			$anmeldungen = $anmeldung->getAnmeldungenByStudent($uid, $pruefungsfenster->studiensemester_kurzbz);

			if($anmeldungen !== false)
			{
				$ects_verwendet = 0;
				foreach($anmeldungen as $temp)
				{
					$lehrveranstaltung = new lehrveranstaltung($temp->lehrveranstaltung_id);
					$ects_verwendet += $lehrveranstaltung->ects;

					$datum = new datum();
					if(($datum->between($termin->von, $termin->bis, $temp->von)) || ($datum->between($termin->von, $termin->bis, $temp->bis)))
					{
						$data['result'][$temp->pruefungstermin_id] = "true";
						$data['error'] = 'true';
						$data['errormsg'] = $p->t('pruefung/kollisionMitAndererAnmeldung');
					}
				}

				$konto = new konto();
				$creditPoints = $konto->getCreditPointsOfStudiensemester($uid, $pruefungsfenster->studiensemester_kurzbz);
				if(($creditPoints !== false))
				{
					$cpVerbleibend = $creditPoints - $ects_verwendet;
					if(($lehrveranstaltung->ects > $cpVerbleibend))
					{
						$data['error'] = 'true';
						$data['errormsg'] = $p->t('pruefung/zuWenigeCreditPoints');
						return $data;
					}
				}
				elseif($konto->errormsg !== null)
				{
					$data['error'] = 'true';
					$data['errormsg'] = 'Fehler beim Laden der Credit Points.';
				}

				if(isset($data['error']) && $data['error'] = 'true')
				{
					return $data;
				}
			}
			else
			{
				$data['error'] = 'true';
				$data['errormsg'] = $anmeldung->errormsg;
				return $data;
			}
		}
		else
		{
			$data['error']='true';
			$data['errormsg']=$p->t('pruefung/keineFreienPlaetzeVorhanden');
			return $data;
		}
	}
	else
	{
		$data['error']='true';
		$data['errormsg']=$p->t('pruefung/anmeldungAufgrundVonSperreNichtMoeglich');
		return $data;
	}

	$anrechnung = new anrechnung();
	$lv_komp = new lehrveranstaltung($studienverpflichtung_id);
	$lehrveranstaltung = new lehrveranstaltung($_REQUEST["lehrveranstaltung_id"]);
	$person = new person();
	$person->getPersonFromBenutzer($uid);
	$prestudent = new prestudent();
	$prestudent->getPrestudenten($person->person_id);
	$studiensemester = new studiensemester();
	$stdsem = $studiensemester->getaktorNext();
	if ($aktStudiensemester)
		$stdsem = $aktStudiensemester;

	$prestudenten = array();
	$gueltigerStatus = array("Student", "Unterbrecher", "Absolvent");

	foreach ($prestudent->result as $ps)
	{
		// prüfen ob Student zum Zeitpunkt der LV oder zumindest irgendwann Student im Studiengang war/ist
		if ($ps->getLaststatus($ps->prestudent_id, $stdsem_lv_besuch) || $ps->studiengang_kz == $studiengang_kz)
		{
			if (in_array($ps->status_kurzbz, $gueltigerStatus) || ($ps->status_kurzbz == ""))
			{
				array_push($prestudenten, $ps);
			}
		}
	}

	if (count($prestudenten) > 0)
	{
		$prestudent_id = "";
		if (count($prestudenten) != 1)
		{
			foreach ($prestudenten as $ps)
			{
				if ($ps->getLaststatus($ps->prestudent_id, $stdsem))
				{
					if (in_array($ps->status_kurzbz, $gueltigerStatus))
					{
						$prestudent_id = $ps->prestudent_id;
					}
					else
					{
						if ($ps->getLaststatus($ps->prestudent_id, $stdsem_lv_besuch))
						{
							if (in_array($ps->status_kurzbz, $gueltigerStatus))
							{
								$prestudent_id = $ps->prestudent_id;
							}
						}
					}
				}
				else
				{
					if ($ps->getLaststatus($ps->prestudent_id, $stdsem_lv_besuch))
					{
						if (in_array($ps->status_kurzbz, $gueltigerStatus))
						{
							$prestudent_id = $ps->prestudent_id;
						}
					}
				}
			}
		}
		else
		{
			foreach ($prestudenten as $ps)
			{
				if ($ps->getLaststatus($ps->prestudent_id, $stdsem))
				{
					if (in_array($ps->status_kurzbz, $gueltigerStatus))
					{
						$prestudent_id = $ps->prestudent_id;
					}
					else
					{
						if ($ps->getLaststatus($ps->prestudent_id, $stdsem_lv_besuch))
						{
							if (in_array($ps->status_kurzbz, $gueltigerStatus))
							{
								$prestudent_id = $ps->prestudent_id;
							}
						}
					}
				}
				else
				{
					if ($ps->getLaststatus($ps->prestudent_id, $stdsem_lv_besuch))
					{
						if (in_array($ps->status_kurzbz, $gueltigerStatus))
						{
							$prestudent_id = $ps->prestudent_id;
						}
					}
				}
			}
		}

		if($prestudent_id != "")
		{
				$anrechungSaveResult = false;
				if(!defined('CIS_PRUEFUNGSANMELDUNG_ANRECHNUNG') || CIS_PRUEFUNGSANMELDUNG_ANRECHNUNG == true)
				{
					if($lv_komp->lehrveranstaltung_id != null && ($lv_komp->lehrveranstaltung_id != $lehrveranstaltung->lehrveranstaltung_id))
					{
						$anrechnung->lehrveranstaltung_id = $lv_komp->lehrveranstaltung_id;
						$anrechnung->lehrveranstaltung_id_kompatibel = $lehrveranstaltung->lehrveranstaltung_id;
						$anrechnung->prestudent_id = $prestudent_id;
						$anrechnung->begruendung_id = "2";
						$anrechnung->genehmigt_von = CIS_PRUEFUNGSANMELDUNG_USER;
						$anrechnung->new = true;
						$anrechungSaveResult = $anrechnung->save();
					}
					else
					{
						$anrechungSaveResult = true;
					}
				}
				else
				{
					$anrechungSaveResult = true;
				}

			if($anrechungSaveResult)
			{
					if($anrechnung->anrechnung_id == "")
						$anmeldung->anrechnung_id = null;
					else
						$anmeldung->anrechnung_id = $anrechnung->anrechnung_id;

					if($anmeldung->save(true))
					{
						$pruefung = new pruefungCis($termin->pruefung_id);
						if(defined('CIS_PRUEFUNG_MAIL_EMPFAENGER_ANMEDLUNG') && (CIS_PRUEFUNG_MAIL_EMPFAENGER_ANMEDLUNG !== ""))
						$to = CIS_PRUEFUNG_MAIL_EMPFAENGER_ANMEDLUNG."@".DOMAIN;
						else
						$to = $pruefung->mitarbeiter_uid."@".DOMAIN;
						$from = "noreply@".DOMAIN;
						$subject = $p->t('pruefung/emailLektorSubjectAnmeldung');
						$mail = new mail($to, $from, $subject, $p->t('pruefung/emailBodyBitteHtmlSicht'));

						$student = new student($uid);
						$datum = new datum();

						$lv = new lehrveranstaltung($anmeldung->lehrveranstaltung_id);

						$html = $p->t('pruefung/emailLektorStudentIn')." ".$student->vorname." ".$student->nachname." ".$p->t('pruefung/emailLektorHatSichZurPruefung')." ".$lv->bezeichnung." ".$p->t('pruefung/emailLektorAm')." ".$datum->formatDatum($termin->von, "d.m.Y")." ".$p->t('pruefung/emailLektorVon')." ".$datum->formatDatum($termin->von,"H:i")." ".$p->t('pruefung/emailLektorUhrBis')." ".$datum->formatDatum($termin->bis,"H:i")." ".$p->t('pruefung/emailLektorUhrAngemeldet');
						$mail->setHTMLContent($html);
						$mail->send();

						$data['result'] = $p->t('pruefung/anmeldungErfolgreich');
						$data['error']='false';
						$data['errormsg']='';
					}
					else
					{
						$data['error']='true';
						$data['errormsg']=$anmeldung->errormsg;
					}
			}
			else
			{
			$data['error']='true';
			$data['errormsg']=$anrechnung->errormsg;
			}
		}
		else
		{
			$data['error']='true';
			$data['errormsg']=$p->t('pruefung/prestudentNichtGefunden');
		}
	}
	else
	{
		$data['error']='true';
		$data['errormsg']=$p->t('pruefung/prestudentNichtGefunden');
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
		if(!empty($lehreinheiten))
		{
		$lveranstaltung = new lehrveranstaltung($lehreinheiten[0]->lehrfach_id);
		$oe = new organisationseinheit($lveranstaltung->oe_kurzbz);
		$prf->organisationseinheit = $oe->bezeichnung;

		// nur hinzufügen wenn zumindest 1 Termin vorhanden ist
		if (!empty($prf->pruefung->termine))
			array_push($pruefungen, $prf);
		}
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
	global $p;
	$pruefungsanmeldung_id=$_REQUEST['pruefungsanmeldung_id'];
	$pruefungsanmeldung = new pruefungsanmeldung($pruefungsanmeldung_id);
	$anrechnung = new anrechnung($pruefungsanmeldung->anrechnung_id);
	if($pruefungsanmeldung->delete($pruefungsanmeldung_id, $uid))
	{
	if($anrechnung->delete($anrechnung->anrechnung_id))
	{
		$data['result'] = $p->t('pruefung/anmeldungErfolgreichGeloescht');
		$data['error'] = 'false';
		$data['errormsg'] = '';
	}
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
function getAnmeldungenTermin()
{
	global $p;
	$lehrveranstaltung_id = $_REQUEST["lehrveranstaltung_id"];
	$pruefungstermin_id = $_REQUEST["pruefungstermin_id"];
	$pruefungstermin = new pruefungstermin($pruefungstermin_id);
	$pruefungsanmeldung = new pruefungsanmeldung();
	$pruefungstermin->anmeldungen = $pruefungsanmeldung->getAnmeldungenByTermin($pruefungstermin_id, $lehrveranstaltung_id);
	$lv = new lehrveranstaltung($lehrveranstaltung_id);
	$pruefungstermin->lv_bezeichnung = $lv->bezeichnung;
	$pruefungstermin->lv_lehrtyp = $lv->lehrtyp_kurzbz;
	$datum = new DateTime($pruefungstermin->von);
	$pruefungstermin->datum = $datum->format('d.m.Y');
	foreach($pruefungstermin->anmeldungen as $a)
	{
		$student = new student($a->uid);
		$temp = new stdClass();
		$temp->vorname = $student->vorname;
		$temp->nachname = $student->nachname;
		$temp->uid = $student->uid;
		$a->student = $temp;
	}
	if(!empty($pruefungstermin->anmeldungen))
	{
		$data['result']=$pruefungstermin;
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
			$data['errormsg']= $p->t('pruefung/keineAnmeldungenVorhanden');
			$data['lv_id'] = $lehrveranstaltung_id;
			$data['termin_id'] = $pruefungstermin_id;
			$data['termin_datum'] = $pruefungstermin->datum;
			$data['lv_bezeichnung'] = $pruefungstermin->lv_bezeichnung;
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
 * Ändert den Status aller Prüfungsanmeldungen eines Termins/LV auf "bestaetigt"
 * @return Array
 */
function alleBestaetigen($uid)
{
	global $p;
	$lehrveranstaltung_id = $_REQUEST["lehrveranstaltung_id"];
	$pruefungstermin_id = $_REQUEST["termin_id"];
	$pruefungstermin = new pruefungstermin($pruefungstermin_id);
	$pruefungsanmeldung = new pruefungsanmeldung();
	$pranmeldungen = $pruefungsanmeldung->getAnmeldungenByTermin($pruefungstermin_id, $lehrveranstaltung_id);
	foreach($pranmeldungen as $a)
	{
		$anmeldung = new pruefungsanmeldung($a->pruefungsanmeldung_id);
		if ($anmeldung->status_kurzbz == 'angemeldet')
		{
			if ($anmeldung->changeState($a->pruefungsanmeldung_id, 'bestaetigt', $uid))
			{
				$anm = new pruefungsanmeldung($a->pruefungsanmeldung_id);
				$termin = new pruefungstermin($anm->pruefungstermin_id);
				$lv = new lehrveranstaltung($anm->lehrveranstaltung_id);
				$ma = new mitarbeiter($uid);
				$datum = new datum();
				$ort = new ort($termin->ort_kurzbz);
				$pruefung = new pruefungCis($termin->pruefung_id);

				$to = $anm->uid."@".DOMAIN;
				$from = "noreply@".DOMAIN;
				$subject = $p->t('pruefung/emailSubjectAnmeldungBestaetigung');
				$html = $p->t('pruefung/emailBody1')." ".$ma->vorname." ".$ma->nachname." ".$p->t('pruefung/emailBody2')."<br>";
				$html .= "<br>";
				$html .= $p->t('pruefung/emailBodyPruefung')." ".$lv->bezeichnung."<br>";
				if($pruefung->einzeln)
				{
					$date = $datum->formatDatum($termin->von, "Y-m-d H:i:s");
					$date = strtotime($date);
					$date = $date+(60*$pruefung->pruefungsintervall*($anmeldung->reihung-1));
					$von = date("H:i",$date);
					$html .= $p->t('pruefung/emailBodyTermin')." ".$datum->formatDatum($termin->von, "d.m.Y")." ".$p->t('pruefung/emailBodyUm')." ".$von."<br>";
					$html .= $p->t('pruefung/emailBodyDauer')." ".$pruefung->pruefungsintervall." ".$p->t('pruefung/emailBodyMinuten')."<br />";
				}
				else
					$html .= $p->t('pruefung/emailBodyTermin')." ".$datum->formatDatum($termin->von, "d.m.Y")." ".$p->t('pruefung/emailBodyUm')." ".$datum->formatDatum($termin->von, "H:i")."<br>";
				$html .= $p->t('pruefung/anmeldungErfolgreich')." ".$ort->bezeichnung."<br>";
				$html .= "<br>";
				$html .= "<a href='".APP_ROOT."cis/private/lehre/pruefung/pruefungsanmeldung.php'>".$p->t('pruefung/emailBodyLinkZurAnmeldung')."</a><br>";
				$html .= "<br>";

				$mail = new mail($to, $from, $subject,$p->t('pruefung/emailBodyBitteHtmlSicht'));
				$mail->setHTMLContent($html);
				$mail->send();
			}
		}
	}
	$data['result']=true;
	$data['error']='false';
	$data['errormsg']='';
	return $data;
}

/**
 * Ändert den Status einer Prüfungsanmeldung auf "bestaetigt"
 * @return Array
 */
function anmeldungBestaetigen($uid)
{
	global $p;
	$pruefungsanmeldung_id = $_REQUEST["pruefungsanmeldung_id"];
	$status = "bestaetigt";
	$anmeldung = new pruefungsanmeldung();
	if($anmeldung->changeState($pruefungsanmeldung_id, $status, $uid))
	{
	$anmeldung = new pruefungsanmeldung($pruefungsanmeldung_id);
	$termin = new pruefungstermin($anmeldung->pruefungstermin_id);
	$lv = new lehrveranstaltung($anmeldung->lehrveranstaltung_id);
	$ma = new mitarbeiter($uid);
	$datum = new datum();
	$ort = new ort($termin->ort_kurzbz);
	$pruefung = new pruefungCis($termin->pruefung_id);

	$to = $anmeldung->uid."@".DOMAIN;
	$from = "noreply@".DOMAIN;
	$subject = $p->t('pruefung/emailSubjectAnmeldungBestaetigung');
	$html = $p->t('pruefung/emailBody1')." ".$ma->vorname." ".$ma->nachname." ".$p->t('pruefung/emailBody2')."<br>";
	$html .= "<br>";
	$html .= $p->t('pruefung/emailBodyPruefung')." ".$lv->bezeichnung."<br>";
	if($pruefung->einzeln)
	{
		$date = $datum->formatDatum($termin->von, "Y-m-d H:i:s");
		$date = strtotime($date);
		$date = $date+(60*$pruefung->pruefungsintervall*($anmeldung->reihung-1));
		$von = date("H:i",$date);
		$html .= $p->t('pruefung/emailBodyTermin')." ".$datum->formatDatum($termin->von, "d.m.Y")." ".$p->t('pruefung/emailBodyUm')." ".$von."<br>";
		$html .= $p->t('pruefung/emailBodyDauer')." ".$pruefung->pruefungsintervall." ".$p->t('pruefung/emailBodyMinuten')."<br />";
	}
	else
		$html .= $p->t('pruefung/emailBodyTermin')." ".$datum->formatDatum($termin->von, "d.m.Y")." ".$p->t('pruefung/emailBodyUm')." ".$datum->formatDatum($termin->von, "H:i")."<br>";
	$html .= $p->t('pruefung/anmeldungErfolgreich')." ".$ort->bezeichnung."<br>";
	$html .= "<br>";
	$html .= "<a href='".APP_ROOT."cis/private/lehre/pruefung/pruefungsanmeldung.php'>".$p->t('pruefung/emailBodyLinkZurAnmeldung')."</a><br>";
	$html .= "<br>";

	$mail = new mail($to, $from, $subject,$p->t('pruefung/emailBodyBitteHtmlSicht'));
	$mail->setHTMLContent($html);
	$mail->send();

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
 * Löscht eine Prüfungsanmeldung
 * @return Array
 */
function anmeldungLoeschen()
{
	$pruefungsanmeldung_id = $_REQUEST["pruefungsanmeldung_id"];
	$anmeldung = new pruefungsanmeldung();

	if($anmeldung->delete($pruefungsanmeldung_id))
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
function getPruefungenStudiengang($uid, $aktStudiensemester)
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
//		var_dump($aktStudiensemester);
//		var_dump($pruefung->studiensemester_kurzbz);
		if(($pruefung->storniert === true))
		{
			unset($pruefung->lehrveranstaltungen[$key]);
		}
		else
		{
			$pruefung->getTermineByPruefung();
			array_push($lv->pruefung, $pruefung);
		}
		}
		if($pruefung->studiensemester_kurzbz === $aktStudiensemester)
		array_push($result, $lv);
	}
	}
	$data['result']=$result;
	$data['error']='false';
	$data['errormsg']='';
	return $data;
}

/**
 *
 * @return typespeichert ein Kommentar zu einer Prüfungsanmeldung
 */
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

/**
 * liefert alle freien Räume für einen Prüfungstermin
 */
function getAllFreieRaeume($terminId)
{
	$pruefungstermin = new pruefungstermin();
	$pruefungstermin->load($terminId);
	$ort = new ort();
	$datum_von = explode(" ", $pruefungstermin->von);
	$datum_bis = explode(" ", $pruefungstermin->bis);
	$teilnehmer = $pruefungstermin->getNumberOfParticipants();
	$teilnehmer = $teilnehmer !== false ? $teilnehmer : 0;
	$pruefungstermin->getAll($pruefungstermin->von, $pruefungstermin->bis, TRUE);

	if(defined('CIS_PRUEFUNGSANMELDUNG_ERLAUBE_TERMINKOLLISION') && CIS_PRUEFUNGSANMELDUNG_ERLAUBE_TERMINKOLLISION)
		$ortSuccess = $ort->getOrte(true, null, true);
	else
		$ortSuccess = $ort->search($datum_von[0], $datum_von[1], $datum_bis[1], null, $teilnehmer, true);

	if($ortSuccess)
	{
	foreach($pruefungstermin->result as $termin)
	{
		if($termin->pruefungstermin_id != $pruefungstermin->pruefungstermin_id && !is_null($termin->ort_kurzbz))
		{
		$o = new ort($termin->ort_kurzbz);
		$o->ort_kurzbz .= " (Sammelklausur)";
		array_push($ort->result, $o);
		}
	}

	usort($ort->result, "compareRaeume");
	$data['result']=$ort->result;
	$data['error']='false';
	$data['errormsg']='';
	}
	else
	{
	$data['error']='true';
	$data['errormsg']=$ort->errormsg;
	}
	return $data;
}

/**
 * vergleicht die Kurzbezeichnungen von 2 Räumen
 * @param $a Ort-Objekt
 * @param $b Ort-Objekt
 * @return $a < $b Wert < 0; $a > $b Wert > 0; $a = $b Wert 0
 */
function compareRaeume($a, $b)
{
	return strcmp($a->ort_kurzbz, $b->ort_kurzbz);
}

function saveRaum($terminId, $ort_kurzbz, $uid)
{
	$terminkollision = defined('CIS_PRUEFUNGSANMELDUNG_ERLAUBE_TERMINKOLLISION') ? CIS_PRUEFUNGSANMELDUNG_ERLAUBE_TERMINKOLLISION : false;
	$pruefungstermin = new pruefungstermin($terminId);
	$stunde = new stunde();
	$datum_von = explode(" ", $pruefungstermin->von);
	$datum_bis = explode(" ", $pruefungstermin->bis);
	$stunden = $stunde->getStunden($datum_von[1], $datum_bis[1]);
	$reservierung = new reservierung();
	$reserviert = false;
	foreach($stunden as $h)
	{
	if($reservierung->isReserviert($ort_kurzbz, $datum_von[0], $h))
		$reserviert = true;
	}
	if($terminkollision || !$reserviert || $pruefungstermin->sammelklausur == TRUE)
	{
	$pruefung = new pruefungCis($pruefungstermin->pruefung_id);
	$mitarbeiter = new mitarbeiter($pruefung->mitarbeiter_uid);
	if($ort_kurzbz === "buero")
	{
		$pruefungstermin->ort_kurzbz = $mitarbeiter->ort_kurzbz;
		if($pruefungstermin->save(false))
		{
		$data['result']="reserviert";
		$data['error']='false';
		$data['errormsg']='';
		}
		else
		{
		$data['error']='true';
		$data['errormsg']=$pruefungstermin->errormsg;
		}
	}
	else
	{
		$reservierung->studiengang_kz = "0";
		$reservierung->ort_kurzbz = $ort_kurzbz;
		$reservierung->uid = $pruefung->mitarbeiter_uid;
		$reservierung->datum = $datum_von[0];
		$reservierung->titel = $pruefung->titel;
		if(strlen($pruefung->titel) > 10)
		{
		$reservierung->titel = "Prüfung";
		}
		$reservierung->beschreibung = "Prüfung";
		$reservierung->insertamum = date('Y-m-d G:i:s');
		$reservierung->insertvon = $uid;
		$reservierungError = false;

		foreach($stunden as $h)
		{
		$reservierung->stunde = $h;
		if(!$reservierung->save(true))
		{
			$reservierungError = true;
		}
		}
		if(!$reservierungError)
		{
		$pruefungstermin->ort_kurzbz = $reservierung->ort_kurzbz;
		if($pruefungstermin->save(false))
		{
			$data['result']="reserviert";
			$data['error']='false';
			$data['errormsg']='';
		}
		else
		{
			$data['error']='true';
			$data['errormsg']=$pruefungstermin->errormsg;
		}
		}
		else
		{
		$data['error']='true';
		$data['errormsg']=$reservierung->errormsg;
		}
	}
	}
	else
	{
	$data['error']='true';
	$data['errormsg']="Reservierung nicht möglich.";
	}
	return $data;
}

function getLvKompatibel($lvid, $uid)
{
	$person = new person();
	$person->getPersonFromBenutzer($uid);
	$prestudent = new prestudent();
	$prestudent->getPrestudenten($person->person_id);

	$stplIds = array();

	foreach ($prestudent->result as $ps)
	{
		if ($ps->getLaststatus($ps->prestudent_id))
		{
			if (($ps->status_kurzbz == "Student") || ($ps->status_kurzbz == "Unterbrecher"))
			{
				array_push($stplIds, $ps->studienplan_id);
			}
		}
	}

	$lv = new lehrveranstaltung();
	if($lv->getLVkompatibelTo($lvid, $stplIds))
	{
		$data['result']=$lv->lehrveranstaltungen;
		$data['error']='false';
		$data['errormsg']='';
	}
	else
	{
		$data['result']="";
		$data['error']='true';
		$data['errormsg']=$lv->errormsg;
	}
	return $data;
}

function getPrestudenten($uid, $aktStudiensemester)
{
	$person = new person();
	$person->getPersonFromBenutzer($uid);
	$prestudent = new prestudent();
	$prestudent->getPrestudenten($person->person_id);
	$result = array();

	if (count($prestudent->result) > 0)
	{
		foreach ($prestudent->result as $key=>$ps)
		{
			if ($ps->getLaststatus($ps->prestudent_id))
			{
				if(($ps->status_kurzbz === 'Student') || ($ps->status_kurzbz == 'Unterbrecher'))
				{
					$studiengang = new studiengang($ps->studiengang_kz);
					array_push($result, $studiengang);
				}
			}
		}

		$data['result']=$result;
		$data['error']='false';
		$data['errormsg']='';
	}
	else
	{
		$data['result']="";
		$data['error']='true';
		$data['errormsg']=$lv->errormsg;
	}

	return $data;
}
?>
