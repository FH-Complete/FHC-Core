<?php
require_once('../config/vilesci.config.inc.php'); 
require_once('../include/basis_db.class.php');

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
				$this->PersKz_Antwort = $row->matrikelnr; 
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
				$this->PersKz_Antwort = $row->matrikelnr; 
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
				$this->PersKz_Antwort = $row->matrikelnr; 
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