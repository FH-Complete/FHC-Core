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
require_once('../include/basis_db.class.php');
require_once('../include/prestudent.class.php');
require_once('../include/student.class.php'); 
require_once('../include/konto.class.php');
require_once('../include/datum.class.php');
require_once('../include/benutzer.class.php');
require_once('../include/webservicelog.class.php'); 
require_once('../include/mail.class.php');
require_once('stip.class.php'); 

ini_set("soap.wsdl_cache_enabled", "0");

$SOAPServer = new SoapServer(APP_ROOT."/soap/stip.wsdl.php?".microtime());
$SOAPServer->addFunction("GetStipendienbezieherStip");
$SOAPServer->addFunction("SendStipendienbezieherStipError");
$SOAPServer->handle();

/**
 * 
 * Funktion nimmt ANfragen entgegen und bearbeitet diese
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
			$StipBezieher->SVNR = $BezieherStip->SVNR; 	
			$StipBezieher->Familienname = $BezieherStip->Familienname; 
			$StipBezieher->Vorname = $BezieherStip->Vorname; 
			$StipBezieher->Typ = $BezieherStip->Typ; 
			
			// Studiensemester_kurzbz auslesen
			if($BezieherStip->Semester == "WS" || $BezieherStip->Semester == "ws")
			{
				$year = mb_substr($BezieherStip->Studienjahr, 0,4); 
				$studSemester = "WS".$year; 
			}elseif ($BezieherStip->Semester == "SS" || $BezieherStip->Semester == "ss")
			{
				$year = mb_substr($BezieherStip->Studienjahr, 0,4); 
				$studSemester = "SS".$year; 
			}
			
			if(!$prestudentID = $StipBezieher->searchPersonKz($BezieherStip->PersKz))
				if(!$prestudentID = $StipBezieher->searchSvnr($BezieherStip->SVNR))
					$prestudentID = $StipBezieher->searchVorNachname($BezieherStip->Vorname, $BezieherStip->Familienname);

			// Student wurde gefunden
			if($StipBezieher->AntwortStatusCode == 1)
			{
				$prestudent = new prestudent(); 
				$prestudent->load($prestudentID); 
				$prestudent->getLastStatus($prestudentID); 
				$prestudentStatus = new prestudent();
			
				$student = new student(); 
				$studentUID = $student->getUID($prestudentID); 

				$student->load($studentUID); 
			
				$konto = new konto(); 
				$studGebuehr = $konto->getStudiengebuehrGesamt($studentUID, $studSemester);
				// , als Dezimaltrennzeichen
				$studGebuehr = str_replace('.', ',', $studGebuehr); 
				
				// wenn nicht bezahlt
				if($studGebuehr == "")
					$studGebuehr = "0,00";
				
				if($BezieherStip->Typ == "as" || $BezieherStip->Typ == "AS")
				{
					$StipBezieher->getOrgFormTeilCode($studentUID, $studSemester);
					
					// Wenn Studium ist VBB -> in Status vom Studenten schauen
					if($StipBezieher->OrgFormTeilCode == 3)
					{
						// lade letzten status vom gesuchten semester
						$prestudentStatus->getLastStatus($prestudentID,$studSemester); 
						$statusOrgForm = $prestudentStatus->orgform_kurzbz; 
						$StipBezieher->OrgFormTeilCode = $StipBezieher->getOrgFormCodeFromKurzbz($statusOrgForm);

					}
					
					$StipBezieher->Studienbeitrag = $studGebuehr; 
					$StipBezieher->Inskribiert ="j";
					$StipBezieher->Ausbildungssemester = $StipBezieher->getSemester($prestudentID, $studSemester);						
					$StipBezieher->StudStatusCode = $StipBezieher->getStudStatusCode($prestudentID, $studSemester);
					if($StipBezieher->StudStatusCode==3 || $StipBezieher->StudStatusCode==4)
						$StipBezieher->BeendigungsDatum = $datum_obj->formatDatum($prestudent->datum,'dmY');
					else 
						$StipBezieher->BeendigungsDatum = null; 
						
					$StipBezieher->Erfolg = $StipBezieher->getErfolg($prestudentID, $studSemester);
				}
				elseif($Bezieher->Typ ="ag" || $Bezieher->Typ == "AG")
				{
					$StipBezieher->Ausbildungssemester = null;
					$StipBezieher->StudStatusCode = null; 
					$StipBezieher->BeendigungsDatum = null; 
					$StipBezieher->Studienbeitrag = null; 
					$StipBezieher->Inskribiert ="j";
					$StipBezieher->OrgFormTeilCode = null; 
				}
				
				$StipBezieherAntwort[$i] = $StipBezieher; 
				$i++;

			}else if($StipBezieher->AntwortStatusCode == 2)
			{
				// Student wurde nicht gefunden
				$StipBezieher->PersKz_Antwort = null; 
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

		}else
		return new SoapFault("Server", $StipBezieher->errormsg);	
	}
	$ret = array("GetStipendienbezieherStipResult" =>array("ErhKz"=>$ErhalterKz,"AnfragedatenID"=>$AnfrageDatenID, "Stipendiumsbezieher"=>$StipBezieherAntwort));
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
	
	$mail = new mail('burkhart@technikum-wien.at', 'vilesci.technikum-wien.at', 'STIP - Error', $xmlData);
	$mail->send();
}

?>


