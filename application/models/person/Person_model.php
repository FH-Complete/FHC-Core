<?php

class Person_model extends DB_Model
{
	// 
	protected $_loadQuery = "SELECT person_id,
								    sprache,
								    anrede,
								    titelpost,
								    titelpre,
								    nachname,
								    vorname,
								    vornamen,
								    gebdatum,
								    gebort,
								    gebzeit,
								    foto,
								    anmerkung,
								    homepage,
								    svnr,
								    ersatzkennzeichen,
								    familienstand,
								    anzahlkinder,
								    aktiv,
								    insertamum,
								    insertvon,
								    updateamum,
								    updatevon,
								    ext_id,
								    geschlecht,
								    staatsbuergerschaft,
								    geburtsnation,
								    kurzbeschreibung,
								    zugangscode,
								    foto_sperre,
								    matr_nr
							   FROM public.tbl_person
							  WHERE person_id = ?";
	
	/**
	 * 
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * 
	 */
	public function getPerson($personID = NULL, $code = NULL, $email = NULL)
	{
		$result = NULL;
		
		// Checks if the operation is permitted by the API caller
		// All the code should be put inside this if statement
		if($this->_checkPermissions())
		{
			if((!is_null($code)) && (!is_null($email)))
			{
				$result = $this->_getPersonByCodeAndEmail($code, $email);
			}
			elseif(!is_null($code))
			{
				$result = $this->_getPersonByCode($code);
			}
			else
			{
				$result = $this->_getPersonByID($personID);
			}
		}
		
		return $result;
	}
	
	/**
	 * @param int $personID Person ID
	 * @return object
	 */
	private function _getPersonByID($personID)
	{
		$result = NULL;
		
		if(!is_null($personID))
		{
			$result = $this->db->query($this->_loadQuery, array($personID));
		}
		
		return $result;
	}

	/**
	 * 
	 */
	private function _getPersonByCodeAndEmail($code, $email)
	{
		$this->db->select("*")
				->from('public.tbl_person p')
				->join("public.tbl_kontakt k", "k.person_id=p.person_id")
				->where("p.zugangscode", $code)
				->where("k.kontakt", $email);

		return $this->db->get()->result_object();
	}

	/**
	 * 
	 */
	private function _getPersonByCode($code)
	{
		$query = $this->db->get_where('public.tbl_person', array('zugangscode' => $code));
		return $query->result_object();
	}

	/**
	 * 
	 */
	public function savePerson($person = NULL)
	{
		// Checks if the operation is permitted by the API caller
		// All the code should be put inside this if statement
		if($this->_checkPermissions() && isset($person))
		{
			if(isset($person['person_id']))
			{
				return $this->_updatePerson($person);
			}
			else
			{
				return $this->_insertPerson($person);
			}
		}
	}
	
	/**
	 * 
	 */
	private function _insertPerson($person)
	{
		$data = array(
			"vorname" => $person["vorname"],
			"nachname" => $person["nachname"],
			"gebdatum" => $person["gebdatum"],
			"aktiv" => TRUE,
			"zugangscode" => $person["zugangscode"],
			"zugangscode_timestamp" => date('Y-m-d H:i:s'),
			"insertamum" => date('Y-m-d H:i:s'),
			"insertvon" => $person["insertvon"],
		);

		if($this->db->insert("public.tbl_person", $data))
		{
			return $this->db->insert_id();
		}
		else
		{
			return FALSE;
		}
	}
	
	/**
	 * 
	 */
	private function _updatePerson($person)
	{
		$this->db->set("zugangscode", $person["zugangscode"]);
		$this->db->where("person_id", $person["person_id"]);
		if($this->db->update("public.tbl_person"))
		{
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}
	
	/**
	 * Laedt Personendaten eine BenutzerUID
	 * @param	string	$uid	DB-Attr: tbl_benutzer.uid .
	 * @return	bool
	 */
	public function getPersonFromBenutzerUID($uid)
	{

		if(!$this->fhc_db_acl->bb->isBerechtigt('person', 's'))
		{
			$this->db->select('tbl_person.*');
			$this->db->from('public.tbl_person JOIN public.tbl_benutzer USING (person_id)');
			$query = $this->db->get_where(null, array('uid' => $uid));
			return $query->result_object();
		}
	}

	/**
	 * 
	 */
	public function checkBewerbung($email, $studiensemester_kurzbz = NULL)
	{
		$this->db->distinct();

		if(is_null($studiensemester_kurzbz))
		{
			$this->db->select("p.person_id, p.zugangscode, p.insertamum")
					->from("public.tbl_person p")
					->join("public.tbl_kontakt k", "p.person_id=k.person_id")
					->join("public.tbl_benutzer b", "p.person_id=b.person_id", "left")
					->where("k.kontakttyp", 'email')
					->where("(kontakt='" . $email . "'" .
							" OR alias ||'@technikum-wien.at'='" . $email . "'" .
							" OR uid ||'@technikum-wien.at'='" . $email . "')")
					->order_by("p.insertamum", "DESC")
					->limit(1)
			;
		}
		else
		{
			$this->db->select("p.person_id,p.zugangscode,p.insertamum")
					->from("public.tbl_person p")
					->join("public.tbl_kontakt k", "p.person_id=k.person_id")
					->join("public.tbl_benutzer b", "p.person_id=b.person_id", "left")
					->join("public.tbl_prestudent ps", "p.person_id=ps.person_id")
					->join("public.tbl_prestudentstatus pst", "pst.prestudent_id=ps.prestudent_id")
					->where("k.kontakttyp", 'email')
					->where("(kontakt='" . $email . "'" .
							" OR alias ||'@technikum-wien.at'='" . $email . "'" .
							" OR uid ||'@technikum-wien.at'='" . $email . "')")
					->where("studiensemester_kurzbz='" . $studiensemester_kurzbz . "'")
					->order_by("p.insertamum", "DESC")
					->limit(1)
			;
		}
		return $this->db->get()->result_array();
	}

	/**
	 * 
	 */
	public function checkZugangscodePerson($code)
	{
		$this->db->select("p.person_id")
				->from("public.tbl_person p")
				->where("p.zugangscode", $code);
		return $this->db->get()->result_array();
	}
	
	/**
	 * 
	 */
	public function saveInterestedStudent($interestedStudent = NULL)
	{
		// Checks if the operation is permitted by the API caller
		// All the code should be put inside this if statement
		if($this->_checkPermissions() && isset($interestedStudent))
		{
			/*
			$data = array(
				"vorname" => $person["vorname"],
				"nachname" => $person["nachname"],
				"gebdatum" => $person["gebdatum"],
				"aktiv" => TRUE,
				"zugangscode" => $person["zugangscode"],
				"zugangscode_timestamp" => date('Y-m-d H:i:s'),
				"insertamum" => date('Y-m-d H:i:s'),
				"insertvon" => $person["insertvon"],
			);

			if($this->db->insert("public.tbl_person", $data))
			{
				return $this->db->insert_id();
			}
			else
			{
				return FALSE;
			}*/
			
			//$prestudent = new prestudent();
			
			error_log($interestedStudent['zgv_code']);
			
			/*$prestudent->zgv_code = $interestedStudent['zgv_code'];
			
			$prestudent->save();*/
		}
	}
}