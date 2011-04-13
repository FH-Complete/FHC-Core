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
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>,
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *          Karl Burkhart <burkhart@technikum-wien.at>.
 */
 
require_once('../config/vilesci.config.inc.php'); 
require_once('../include/basis_db.class.php');
require_once('../include/prestudent.class.php');
require_once('../include/student.class.php'); 
require_once('../include/konto.class.php');
require_once('../include/datum.class.php');
require_once('stip.class.php'); 

$SOAPServer = new SoapServer("stip.soap.wsdl");
$SOAPServer->addFunction(array("getStipDaten", "getErrorCode"));
$SOAPServer->handle();


function getStipDaten($ErhKz, $AnfragedatenID, $Bezieher)
{ 	
	$prestudentID; 
	$studentUID; 
	$studSemester; 
	$StipBezieher = new stip();
	$datum_obj = new datum(); 
	
	if(validateStipDaten($ErhKz, $AnfragedatenID, $Bezieher))
	{
		$StipBezieher->Semester = $Bezieher->Semester; 
		$StipBezieher->Studienjahr = $Bezieher->Studienjahr; 
		$StipBezieher->PersKz = $Bezieher->PersKz; 
		$StipBezieher->SVNR = $Bezieher->SVNR; 	
		$StipBezieher->Familienname = $Bezieher->Familienname; 
		$StipBezieher->Vorname = $Bezieher->Vorname; 
		$StipBezieher->Typ = $Bezieher->Typ; 
		
		// Studiensemester_kurzbz auslesen
		if($Bezieher->Semester == "WS" || $Bezieher->Semester == "ws")
		{
			$year = mb_substr($Bezieher->Studienjahr, 0,4); 
			$studSemester = "WS".$year; 
		}elseif ($Bezieher->Semester == "SS" || $Bezieher->Semester == "ss")
		{
			$year = mb_substr($Bezieher->Studienjahr, 0,4); 
			$studSemester = "SS".$year; 
		}
		
		if(!$prestudentID = $StipBezieher->searchPersonKz($Bezieher->PersKz))
			if(!$prestudentID = $StipBezieher->searchSvnr($Bezieher->SVNR))
				$prestudentID = $StipBezieher->searchVorNachname($Bezieher->Vorname, $Bezieher->Familienname);
	
		$prestudent = new prestudent(); 
		$prestudent->load($prestudentID); 
			//$prestudent->loadLastStatus
		
		$student = new student(); 
		$studentUID = $student->getUID($prestudentID); 
		$student->load($studentUID); 
		
		$konto = new konto(); 
		$studGebuehr = $konto->getStudiengebuehrGesamt($studentUID, $studSemester);
		
		// Student wurde gefunden
		if($StipBezieher->AntwortStatusCode == 1)
		{
			if($Bezieher->Typ == "as" || $Bezieher->Typ == "AS")
			{
				$StipBezieher->getOrgFormTeilCode($studentUID, $studSemester);
				$StipBezieher->Studienbeitrag = $studGebuehr; 
				$StipBezieher->Inskribiert ="j";
				$StipBezieher->Ausbildungssemester = $StipBezieher->getSemester($prestudentID, $studSemester);
				//return new SoapFault("Server", "Some error message");				
				$StipBezieher->StudStatusCode = $StipBezieher->getStudStatusCode($prestudentID, $studSemester);
				if($StipBezieher->StudStatusCode==3 || $StipBezieher->StudStatusCode==4)
					$StipBezieher->BeendigungsDatum = $datum_obj->formatDatum($prestudent->datum,'dmY');
				$StipBezieher->Erfolg = $StipBezieher->getErfolg($prestudentID, $studSemester);
			}
			elseif($Bezieher->Typ ="ag" || $Bezieher->Typ == "AG")
			{
				
			}
		}
				
		$Erhalter = sprintf("%03d",$StipBezieher->getErhalterKz()); 
			
		$new = array($Erhalter,$AnfragedatenID, $StipBezieher); 
		return $new; 
	}
	
}

function validateStipDaten($ErhKz, $Anfragedaten, $Bezieher)
{
	if(strlen($ErhKz)!=3 || !is_numeric($ErhKz))
		return false; 
		
	if(strlen($Bezieher->Semester)!=2 || ($Bezieher->Semester != "ws" && $Bezieher->Semester != "ss" && $Bezieher->Semester != "WS" && $Bezieher->Semester != "SS"))
		return false; 

	if(strlen($Bezieher->Studienjahr) != 7)
		return false; 
	
	// kein Mussfeld
	if($Bezieher->PersKz != null && strlen($Bezieher->PersKz) != 10)
		return false; 
		
	if(mb_strlen($Bezieher->SVNR) != 10 || !is_numeric($Bezieher->SVNR))
		return false; 
		
		// preg_match funktioniert noch nicht || preg_match_all('[^0-9]*',$Bezieher->Familienname)>0
	if(mb_strlen($Bezieher->Familienname) > 255 || $Bezieher->Familienname == null || mb_strlen($Bezieher->Familienname)<2)
		return false; 
		
	if(mb_strlen($Bezieher->Vorname) > 255 || $Bezieher->Familienname == null || mb_strlen($Bezieher->Vorname) <2)
		return false; 
		
	if(mb_strlen($Bezieher->Typ) != 2 || ($Bezieher->Typ != "ag" && $Bezieher->Typ != "as" && $Bezieher->Typ != "AG" && $Bezieher->Typ != "AS"))
		return false; 
		
		return true; 
}

function getErrorCode($ErhKz, $StateCode, $StateMessage, $ErrorStatusCode, $JobId, $ErrorContent)
{
	return "$ErhKz, $StateCode"; 
}

?>


