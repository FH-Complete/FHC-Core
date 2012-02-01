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
require_once('../include/studiensemester.class.php');
require_once('../include/zeugnisnote.class.php');

class stip extends basis_db
{
	public $Semester; 
	public $Studienjahr; 
	public $PersKz; 
	public $SVNR; 
	public $Familienname; 
	public $Vorname; 
	public $Typ;  
	public $PersKz_Antwort;
	public $SVNR_Antwort; 
	public $Familienname_Antwort; 
	public $Vorname_Antwort; 
	public $Ausbildungssemester; 
	public $StudStatusCode; 
	public $BeendigungsDatum; 
	public $VonNachPersKz; 
	public $Studienbeitrag; 
	public $Inskribiert; 
	public $Erfolg; 
	public $OrgFormTeilCode; 
	public $AntwortStatusCode; 
	
	
	/**
	 * 
	 * Überprüft die Daten
	 * @param $ErhKz
	 * @param $Anfragedaten
	 * @param $Bezieher
	 */
	function validateStipDaten($ErhKz, $Anfragedaten, $Bezieher)
	{
		if(strlen($ErhKz)!=3 || !is_numeric($ErhKz))
		{
			$this->errormsg = "Kein gültiger Wert für ErhKz"; 
			return false; 
		}
			
		if(strlen($Bezieher->Semester)!=2 || ($Bezieher->Semester != "ws" && $Bezieher->Semester != "ss" && $Bezieher->Semester != "WS" && $Bezieher->Semester != "SS"))
		{
			$this->errormsg = "Kein gültiger Wert für Semester"; 
			return false; 
		}
	
		if(strlen($Bezieher->Studienjahr) != 7)
		{
			$this->errormsg = "Kein gültiger Wert für Studienjahr"; 
			return false; 
		}
		
		// kein Mussfeld
		if($Bezieher->PersKz != null && strlen($Bezieher->PersKz) != 10)
		{
			$this->errormsg = "Kein gültiger Wert für PersKz"; 
	//		return false; 
		}
			
		if(mb_strlen($Bezieher->SVNR) != 10 || !is_numeric($Bezieher->SVNR))
		{
			$this->errormsg = "Kein gültiger Wert für SVNR"; 
		//	return false; 
		}
			
			// preg_match funktioniert noch nicht || preg_match_all('[^0-9]*',$Bezieher->Familienname)>0
		if(mb_strlen($Bezieher->Familienname) > 255 || $Bezieher->Familienname == null || mb_strlen($Bezieher->Familienname)<2)
		{
			$this->errormsg = "Kein gültiger Wert für Familienname"; 
			//return false; 
		}
			
		if(mb_strlen($Bezieher->Vorname) > 255 || $Bezieher->Vorname == null || mb_strlen($Bezieher->Vorname) <2)
		{
			$this->errormsg = "Kein gültiger Wert für Vorname"; 
		//	return false; 
		}
			
		if(mb_strlen($Bezieher->Typ) != 2 || ($Bezieher->Typ != "ag" && $Bezieher->Typ != "as" && $Bezieher->Typ != "AG" && $Bezieher->Typ != "AS"))
		{
			$this->errormsg = "Kein gültiger Wert für Typ"; 
			return false; 
		}
			
			return true; 
	}
		
	
	/**
	 * 
	 * Suche Studenten anhand PersonKz
	 * @param $PersonKz
	 */
	function searchPersonKz($PersonKz)
	{
		$qry = "Select prestudent_id, vorname, nachname, svnr, matrikelnr from public.tbl_student student 
		join public.tbl_benutzer benutzer on(benutzer.uid=student.student_uid)
		join public.tbl_person person using(person_id)
		where student.matrikelnr = '".addslashes($PersonKz)."';"; 
		
		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$this->Vorname_Antwort = $row->vorname; 
				$this->Familienname_Antwort = $row->nachname; 
				$this->SVNR_Antwort = $row->svnr; 
				$this->PersKz_Antwort = trim($row->matrikelnr); 
				$this->AntwortStatusCode = 1; 
				return $row->prestudent_id; 
			}
			else
			{
				$this->AntwortStatusCode =2; 
				return false; 
			}
		}
		else
		{
			return false; 
		}

	}
	/**
	 * 
	 * Suche Studenten anhand Sozialversicherungsnummer
	 * @param $Svnr
	 */
	function searchSvnr($Svnr)
	{
		$qry = "Select prestudent_id, vorname, nachname, svnr, matrikelnr from public.tbl_student student 
		join public.tbl_benutzer benutzer on(benutzer.uid=student.student_uid)
		join public.tbl_person person using(person_id)
		where person.svnr = '".addslashes($Svnr)."';"; 
		
		if($this->db_query($qry))
		{
			// wenn mehr als 1 Datensatz gefunden wird --> Fehler
			if($this->db_num_rows() == 1 )
			{
				if($row = $this->db_fetch_object())
				{
					$this->Vorname_Antwort = $row->vorname; 
					$this->Familienname_Antwort = $row->nachname; 
					$this->SVNR_Antwort = $row->svnr; 
					$this->PersKz_Antwort = trim($row->matrikelnr); 
					$this->AntwortStatusCode = 1; 
					return $row->prestudent_id; 
				}
				else
				{
					$this->AntwortStatusCode =2; 
					return false; 
				}
			}
			else 
			{
				$this->AntwortStatusCode = 2; 
				return false; 
			}
		}
		else
		{
			return false; 
		}
		return true; 
		
	}
	
	/**
	 * 
	 * Suche Studenten anhand Vor- und Nachname
	 * @param $Svnr
	 */
	function searchVorNachname($Vorname, $Nachname)
	{
		$qry = "Select prestudent_id, vorname, nachname, svnr, matrikelnr from public.tbl_student student 
		join public.tbl_benutzer benutzer on(benutzer.uid=student.student_uid)
		join public.tbl_person person using(person_id)
		where person.vorname = '".addslashes($Vorname)."'
		and person.nachname = '".addslashes($Nachname)."';"; 
		
		if($this->db_query($qry))
		{
			// wenn mehr als 1 Datensatz gefunden wird --> Fehler
			if($this->db_num_rows() == 1 )
			{
				if($row = $this->db_fetch_object())
				{
					$this->Vorname_Antwort = $row->vorname; 
					$this->Familienname_Antwort = $row->nachname; 
					$this->SVNR_Antwort = $row->svnr; 
					$this->PersKz_Antwort = trim($row->matrikelnr); 
					$this->AntwortStatusCode = 1; 
					return $row->prestudent_id; 
				}
				else
				{
					$this->AntwortStatusCode =2; 
					return false; 
				}
			}
			else 
			{
				$this->AntwortStatusCode = 2; 
				return false; 
			}
			
		}
		else
		{
			return false; 
		}
		return true; 
		
	}
	
	/**
	 * 
	 * Gibt Erhalter_Kz für Technikum Wien zurück
	 */
	function getErhalterKz()
	{
		$qry = "Select erhalter_kz from public.tbl_erhalter where kurzbz = 'TW';";
		
		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				return $row->erhalter_kz;
			}
			else 
				return false; 
		}
		else 
			return false;
	}
	
	/**
	 * 
	 * Gibt den orgform_code zurück für übergebene StudentUID und Semester
	 * z.B. 1 für Vollzeit
	 * z.B. 2 für Berufsbegleitend
	 * @param $studentUID
	 * @param $studSemester
	 */
	function getOrgFormTeilCode($studentUID, $studSemester)
	{
		$qry = "select orgform.code, studiengang.orgform_kurzbz as studorgkz, student.student_uid, student.studiengang_kz studiengang
		from public.tbl_studiengang studiengang
		join public.tbl_student student using(studiengang_kz)
		join public.tbl_prestudent prestudent using(prestudent_id)
		join public.tbl_prestudentstatus status using(prestudent_id)
		join bis.tbl_orgform orgform on(orgform.orgform_kurzbz = studiengang.orgform_kurzbz) where student_uid='$studentUID'
		and status.studiensemester_kurzbz ='$studSemester';";
		
		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$this->OrgFormTeilCode = $row->code; 
				return true; 
			}
			return false; 
		}
		else
			return false; 
	}
	
	
	/**
	 * 
	 * Gibt den orgform_code zurück für übergebene orgform_kurzbz
	 * @param orgform_kurzbz
	 */
	function getOrgFormCodeFromKurzbz($orgform_kurzbz)
	{
		$qry = "SELECT code FROM bis.tbl_orgform WHERE orgform_kurzbz = '".addslashes($orgform_kurzbz)."';";
		
		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				return $row->code; 
			}
			return false; 
		}
		else
			return false; 
	}
	
	/**
	 * 
	 * Ermittelt den StutStatusCode
	 * Stati Aktiver Student[Code 1], Unterbrecher[2], Absolvent[3] und Ausgeschieden ohne Abschluss[4]
	 * Stati Übertritt[5] kommt nicht vor
	 */
	public function getStudStatusCode($prestudent_id, $studiensemester_kurzbz, $bisdatum=null)
	{
		$qrystatus="
			SELECT 
				* 
			FROM 
				public.tbl_prestudentstatus
			WHERE 
				prestudent_id='$prestudent_id' 
				AND studiensemester_kurzbz='$studiensemester_kurzbz'";
		if(!is_null($bisdatum))
				$qrystatus.=" AND (tbl_prestudentstatus.datum<'$bisdatum')";
		
		$qrystatus.=" ORDER BY datum desc, insertamum desc, ext_id desc;";
		
		if($resultstatus = $this->db_query($qrystatus))
		{
			if($this->db_num_rows($resultstatus)==0)
			{
				$stsem = new studiensemester();
				$psem = $stsem->getPreviousFrom($studiensemester_kurzbz);
				
				$qrystatus="
					SELECT * 
					FROM 
						public.tbl_prestudentstatus 
					WHERE 
						prestudent_id='$prestudent_id' 
						AND studiensemester_kurzbz='$psem'";
				if(!is_null($bisdatum)) 
					$qrystatus.=" AND (tbl_prestudentstatus.datum<'$bisdatum') ";
				$qrystatus.=" ORDER BY datum desc, insertamum desc, ext_id desc;";
				
				if(!$resultstatus = $this->db_query($qrystatus))
				{
					$this->errormsg='Fehler beim Laden der Daten';
					return false;
				}
			}
		}
		if($rowstatus = $this->db_fetch_object($resultstatus))
		{
			switch($rowstatus->status_kurzbz)
			{
				case 'Student':
				case 'Incoming':
				case 'Outgoing':
				case 'Praktikant':
				case 'Diplomand':
					$status=1;
					break;
				case 'Unterbrecher':
					$status=2;
					break;
				case 'Absolvent':
					$status=3;
					break;
				case 'Abbrecher':
					$status=4;
					break;
				default:
					$this->errormsg='Fehlerhafter Status';
					break;
			}
		}
		
		return $status;
	}
	/**
	 * 
	 * Ermittelt den SemesterCode
	 */
	public function getSemester($prestudent_id, $studiensemester_kurzbz, $bisdatum=null)
	{
		$qrystatus="
			SELECT 
				* 
			FROM 
				public.tbl_prestudentstatus
			WHERE 
				prestudent_id='$prestudent_id' 
				AND studiensemester_kurzbz='$studiensemester_kurzbz'";
		if(!is_null($bisdatum))
				$qrystatus.=" AND (tbl_prestudentstatus.datum<'$bisdatum')";
		
		$qrystatus.=" ORDER BY datum desc, insertamum desc, ext_id desc;";
		
		if($resultstatus = $this->db_query($qrystatus))
		{
			if($this->db_num_rows($resultstatus)==0)
			{
				$stsem = new studiensemester();
				$psem = $stsem->getPreviousFrom($studiensemester_kurzbz);
				
				$qrystatus="
					SELECT * 
					FROM 
						public.tbl_prestudentstatus 
					WHERE 
						prestudent_id='$prestudent_id' 
						AND studiensemester_kurzbz='$psem'";
				if(!is_null($bisdatum)) 
					$qrystatus.=" AND (tbl_prestudentstatus.datum<'$bisdatum') ";
				$qrystatus.=" ORDER BY datum desc, insertamum desc, ext_id desc;";
				
				if(!$resultstatus = $this->db_query($qrystatus))
				{
					$this->errormsg='Fehler beim Laden der Daten';
					return false;
				}
			}
			
			if($rowstatus = $this->db_fetch_object($resultstatus))
			{
				$qry1="
					SELECT count(*) AS dipl FROM public.tbl_prestudentstatus 
					WHERE 
						prestudent_id='$prestudent_id' 
						AND status_kurzbz='Diplomand'";
				if(!is_null($bisdatum))
					$qry1.=" AND (tbl_prestudentstatus.datum<'$bisdatum') ";
				
				if($result1 = $this->db_query($qry1))
				{
					if($row1 = $this->db_fetch_object($result1))
					{
						$sem=$rowstatus->ausbildungssemester;
						
						if($row1->dipl>1)
						{
							$sem=50;
						}
						if($row1->dipl>3)
						{
							$sem=60;
						}
					}
				}
			}
		}
		return $sem;
	}
	
	/**
	 * Liefert die ECTS Punkte
	 * 
	 * @param $prestudent_id
	 * @param $studiensemester_kurzbz
	 */
	public function getErfolg($prestudent_id, $studiensemester_kurzbz)
	{
		$student = new student();
		$uid = $student->getUid($prestudent_id);
		
		$obj = new zeugnisnote();
		$ects=0;
		if(!$obj->getZeugnisnoten($lehrveranstaltung_id=null, $uid, $studiensemester_kurzbz))
			die('Fehler beim Laden der Noten:'.$obj->errormsg);
		foreach($obj->result as $row)
		{
			//Note darf nicht teilnote(0), negativ(5), noch nicht eingetragen(7), nicht beurteilt (9), nicht erfolgreich absolviert (13), angerechnet(6) sein
			if($row->zeugnis && $row->note!=0 && $row->note!=5 && $row->note!=7 && $row->note!=9 && $row->note!=13 && $row->note!=6)
			{
				$ects += $row->ects;
			}
		}
		return number_format($ects,2,',','');
	}
}

class error
{
	public $ErrorNumbe; 
	public $KeyAttribute; 
	public $KeyValues; 
	public $CheckAttribute; 
	public $CheckValue; 
	public $ErrorText; 
}

?>