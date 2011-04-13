<?php
require_once('../config/vilesci.config.inc.php'); 
require_once('../include/basis_db.class.php');
require_once('../include/studiensemester.class.php');

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
	 * Enter description here ...
	 * @param unknown_type $PersonKz
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
	 * Enter description here ...
	 * @param unknown_type $Svnr
	 */
	function searchSvnr($Svnr)
	{
		$qry = "Select prestudent_id, vorname, nachname, svnr, matrikelnr from public.tbl_student student 
		join public.tbl_benutzer benutzer on(benutzer.uid=student.student_uid)
		join public.tbl_person person using(person_id)
		where person.svnr = '".addslashes($Svnr)."';"; 
		
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
		return true; 
		
	}
	
	/**
	 * 
	 * Enter description here ...
	 * @param unknown_type $Svnr
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
		return true; 
		
	}
	
	/**
	 * 
	 * Enter description here ...
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
	 * Enter description here ...
	 * @param unknown_type $studentUID
	 * @param unknown_type $studSemester
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
	 * Ermittelt den StutStatusCode
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