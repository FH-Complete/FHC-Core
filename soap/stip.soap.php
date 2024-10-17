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
 * Authors:		Karl Burkhart <burkhart@technikum-wien.at>.
 */

require_once('../config/vilesci.config.inc.php');
require_once('../config/global.config.inc.php');
require_once('../include/basis_db.class.php');
require_once('../include/prestudent.class.php');
require_once('../include/student.class.php');
require_once('../include/konto.class.php');
require_once('../include/datum.class.php');
require_once('../include/benutzer.class.php');
require_once('../include/webservicelog.class.php');
require_once('../include/mail.class.php');
require_once('../include/abschlusspruefung.class.php');
require_once('../include/note.class.php');
require_once('stip.class.php');

ini_set("soap.wsdl_cache_enabled", "0");

$SOAPServer = new SoapServer(APP_ROOT."/soap/stip.wsdl.php?".microtime(true));
$SOAPServer->addFunction("GetStipendienbezieherStip");
$SOAPServer->addFunction("SendStipendienbezieherStipError");
$SOAPServer->handle();

/**
 *
 * Funktion nimmt Anfragen entgegen und bearbeitet diese
 * @param $parameters -> XML SOAP File
 */
function GetStipendienbezieherStip($parameters)
{
	$anfrageDaten = $parameters->anfrageDaten;
	$Stipendiumsbezieher = $anfrageDaten->Stipendiumsbezieher;

	$ErhalterKz = $anfrageDaten->ErhKz;
	$AnfrageDatenID = $anfrageDaten->AnfragedatenID;

	// Eintrag in der LogTabelle anlegen
	$log = new webservicelog();
	$log->request_data = file_get_contents('php://input');
	$log->webservicetyp_kurzbz = 'stip';
	$log->request_id = $AnfrageDatenID;
	$log->beschreibung = "Anfrage von Stip";
	$log->save(true);

	$username = $parameters->userName;
	$passwort = $parameters->passWord;

	if(!($username==STIP_USER_NAME && $passwort==STIP_USER_PASSWORD))
	{
		// Eintrag in der LogTabelle anlegen
		$log = new webservicelog();
		$log->request_data = 'SOAP FAULT - Invalid Credentials';
		$log->webservicetyp_kurzbz = 'stip';
		$log->request_id = $AnfrageDatenID;
		$log->beschreibung = "Antwort an Stip";
		$log->save(true);

		return new SoapFault("Server", 'Invalid Credentials');
	}

	$StipBezieherAntwort = array();

	$i=0;
	if(!is_array($Stipendiumsbezieher->StipendiumsbezieherAnfrage))
		$Stipendiumsbezieher->StipendiumsbezieherAnfrage = array($Stipendiumsbezieher->StipendiumsbezieherAnfrage);

	// läuft alle Anfragedaten durch
	foreach($Stipendiumsbezieher->StipendiumsbezieherAnfrage as $BezieherStip)
	{
		$prestudentID;
		$studentUID;
		$studSemester;
		$StipBezieher = new stip();
		$datum_obj = new datum();

		if($StipBezieher->validateStipDaten($anfrageDaten->ErhKz, $anfrageDaten->AnfragedatenID, $BezieherStip))
		{
			$StipBezieher->Semester = $BezieherStip->Semester;
			$StipBezieher->Studienjahr = $BezieherStip->Studienjahr;
			$StipBezieher->PersKz = $BezieherStip->PersKz;
			$StipBezieher->Matrikelnummer = $BezieherStip->Matrikelnummer;
			$StipBezieher->StgKz = $BezieherStip->StgKz;
			$StipBezieher->SVNR = $BezieherStip->SVNR;
			$StipBezieher->Familienname = $BezieherStip->Familienname;
			$StipBezieher->Vorname = $BezieherStip->Vorname;
			$StipBezieher->Typ = $BezieherStip->Typ;

			// Studiensemester_kurzbz auslesen
			if ($BezieherStip->Semester == "WS" || $BezieherStip->Semester == "ws")
			{
				$year = mb_substr($BezieherStip->Studienjahr, 0,4);
				$studSemester = "WS".$year;
			}
			elseif ($BezieherStip->Semester == "SS" || $BezieherStip->Semester == "ss")
			{
				$year = mb_substr($BezieherStip->Studienjahr, 0,2).mb_substr($BezieherStip->Studienjahr, 5,7);
				$studSemester = "SS".$year;
			}

			if(!$prestudentID = $StipBezieher->searchPersonKz($BezieherStip->PersKz))
			{
				if(!$prestudentID = $StipBezieher->searchMatrikelnummerStg($BezieherStip->Matrikelnummer, $BezieherStip->StgKz, $studSemester))
				{
					if(!$prestudentID = $StipBezieher->searchSvnr($BezieherStip->SVNR, $BezieherStip->StgKz, $studSemester))
					{
						$prestudentID = $StipBezieher->searchVorNachname($BezieherStip->Vorname, $BezieherStip->Familienname, $BezieherStip->StgKz, $studSemester);
					}
				}
			}

			// Student wurde gefunden
			if($StipBezieher->AntwortStatusCode == 1)
			{
				$prestudent = new prestudent();
				$prestudent->load($prestudentID);
				$prestudent->getLastStatus($prestudentID);
				$prestudentStatus = new prestudent();

				$student = new student();
				$studentUID = $student->getUID($prestudentID);

				$abschlusspruefung = new abschlusspruefung();
				$abschlusspruefung->getLastAbschlusspruefung($studentUID);

				$student->load($studentUID);
				$studiengang_kz = $student->studiengang_kz;

				$konto = new konto();
				$studGebuehr = $konto->getStudiengebuehrGesamt($studentUID, $studSemester, $studiengang_kz);
				// , als Dezimaltrennzeichen
				$studGebuehr = str_replace('.', ',', $studGebuehr);

				// wenn nicht bezahlt
				if($studGebuehr == "")
					$studGebuehr = "0,00";

				if(!$prestudentStatus->getLastStatus($prestudentID,$studSemester))
					$StipBezieher->Inskribiert = 'n';
				else
				{
					// wenn nur Interessent letzer Status ist -> nicht inskribiert
					if($prestudentStatus->status_kurzbz == 'Interessent')
						$StipBezieher->Inskribiert = 'n';
					else
						$StipBezieher->Inskribiert = 'j';
				}

				if($BezieherStip->Typ == "as" || $BezieherStip->Typ == "AS")
				{
					$StipBezieher->getOrgFormTeilCode($studentUID, $studSemester, $prestudentID);
					$StipBezieher->Studienbeitrag = $studGebuehr;

					// Wenn letzter Status von Semester Interessent ist -> Semester = null
					if($prestudentStatus->status_kurzbz != 'Interessent')
						$StipBezieher->Ausbildungssemester = $StipBezieher->getSemester($prestudentID, $studSemester);
					else
						$StipBezieher->Ausbildungssemester = null;

					$StipBezieher->StudStatusCode = $StipBezieher->getStudStatusCode($prestudentID, $studSemester);

					// Ausgeschieden ohne Abschluss
					if($StipBezieher->StudStatusCode==4)
						$StipBezieher->BeendigungsDatum = $datum_obj->formatDatum($prestudent->datum,'dmY');
					else if($StipBezieher->StudStatusCode==3) // Absolvent -> letzte Prüfung nehmen
						$StipBezieher->BeendigungsDatum = $datum_obj->formatDatum($abschlusspruefung->datum,'dmY');
					else
						$StipBezieher->BeendigungsDatum = null;

					$StipBezieher->Erfolg = $StipBezieher->getErfolg($prestudentID, $studSemester);
				}
				elseif($BezieherStip->Typ =="ag" || $BezieherStip->Typ == "AG")
				{
					$StipBezieher->Ausbildungssemester = null;
					$StipBezieher->StudStatusCode = null;
					$StipBezieher->BeendigungsDatum = null;
					$StipBezieher->Studienbeitrag = null;
					$StipBezieher->OrgFormTeilCode = null;
				}

				$StipBezieherAntwort[$i] = $StipBezieher;
				$i++;

			}
			else if($StipBezieher->AntwortStatusCode == 2)
			{
				// Student wurde nicht gefunden
				$StipBezieher->PersKz_Antwort = null;
				$StipBezieher->Matrikelnummer_Antwort = null;
				$StipBezieher->StgKz_Antwort = null;
				$StipBezieher->SVNR_Antwort = null;
				$StipBezieher->Familienname_Antwort = null;
				$StipBezieher->Vorname_Antwort = null;
				$StipBezieher->Ausbildungssemester = null;
				$StipBezieher->StudStatusCode = null;
				$StipBezieher->BeendigungsDatum = null;
				$StipBezieher->VonNachPersKz = null;
				$StipBezieher->Studienbeitrag = null;
				$StipBezieher->Inskribiert = null;
				$StipBezieher->Erfolg = null;
				$StipBezieher->OrgFormTeilCode = null;
				$StipBezieherAntwort[$i] = $StipBezieher;
				$i++;
			}

		}
		else
		{
			// Eintrag in der LogTabelle anlegen
			$log = new webservicelog();
			$log->request_data = 'SOAP FAULT - ValidationError: '.$StipBezieher->errormsg;
			$log->webservicetyp_kurzbz = 'stip';
			$log->request_id = $AnfrageDatenID;
			$log->beschreibung = "Antwort an Stip";
			$log->save(true);

			return new SoapFault("Server", $StipBezieher->errormsg);
		}
	}

	$ret = array("GetStipendienbezieherStipResult" =>array("ErhKz"=>$ErhalterKz,"AnfragedatenID"=>$AnfrageDatenID, "Stipendiumsbezieher"=>$StipBezieherAntwort));

	// Eintrag in der LogTabelle anlegen
	$log = new webservicelog();
	$log->request_data = print_r($ret,true);
	$log->webservicetyp_kurzbz = 'stip';
	$log->request_id = $AnfrageDatenID;
	$log->beschreibung = "Antwort an Stip";
	$log->save(true);

	return $ret;
}

/**
 *
 * Funktion nimmt Fehler entgegen und sendet sie an Admin
 * @param $parameters -> XML SOAP File
 */
function SendStipendienbezieherStipError($parameters)
{
	$xmlData = file_get_contents('php://input');

	$log = new webservicelog();
	$log->request_data = file_get_contents('php://input');
	$log->webservicetyp_kurzbz = 'stip';
	//$log->request_id = $AnfrageDatenID;
	$log->beschreibung = "Stip Error";
	$log->save(true);

	//1=successful; 2=incomplete xml document; 3=incomplete processing; 4=system-error
	if($parameters->errorReport->ErrorStatusCode!=1)
	{
		$mail = new mail(MAIL_ADMIN, 'vilesci@'.DOMAIN, 'STIP - Error', $xmlData);
		$mail->send();
	}
}

?>
