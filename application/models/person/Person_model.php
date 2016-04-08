<?php

class Person_model extends DB_Model
{

    public function __construct($uid = null)
    {
		parent::__construct($uid);
		$this->dbTable = 'public.tbl_person';
    }

    public function getPerson($person_id = null)
    {
		if (is_null($person_id))
		{
			$query = $this->db->get_where('public.tbl_person', array());
			return $query->result_object();
		}

		$query = $this->db->get_where('public.tbl_person', array('person_id' => $person_id));
		return $query->row_object();
	}

	/**
	 * Laedt Personendaten einer Person mittels Code
	 * @param	string	$code	DB-Attr: tbl_benutzer.zugangscode .
	 * @return	object
	 */
	public function getPersonByCode($code)
	{
		if ($this->fhc_db_acl->bb->isBerechtigt('person','s'))
		{
			$query = $this->db->get_where('public.tbl_person', array('zugangscode' => $code));
		    return $query->result_object();
		}
		else
		{
			return $this->_general_error($this->fhc_db_acl->bb->errormsg);
			//return false;
		}
	}

    /**
     * Laedt Personendaten eine BenutzerUID
     * @param	string	$uid	DB-Attr: tbl_benutzer.uid .
     * @return	bool
     */
    public function getPersonFromBenutzerUID($uid)
    {
		if (!$this->fhc_db_acl->bb->isBerechtigt('person', 's'))
		{
			$this->db->select('tbl_person.*');
			$this->db->from('public.tbl_person JOIN public.tbl_benutzer USING (person_id)');
			$query = $this->db->get_where(null, array('uid' => $uid));
			return $query->result_object();
		}
    }
    
    public function savePerson($person)
    {
	//TODO check berechtigung
//	if ($this->fhc_db_acl->bb->isBerechtigt('person', 'sui'))
//	{
	    $data = array(
		"vorname"=>$person["vorname"],
		"nachname"=>$person["nachname"],
		"gebdatum"=>$person["gebdatum"],
		"aktiv" => true,
		"zugangscode"=>$person["zugangscode"],
		"insertamum"=>date('Y-m-d H:i:s'),
		"insertvon"=>$person["insertvon"],
	    );
	    if($this->db->insert("public.tbl_person", $data)){
		return $this->db->insert_id();
	    }
	    else
	    {
		return false;
	    }
//	}
//	else
//	{
//	    return "Nicht berechtigt";
//	}
    }

    public function checkBewerbung($email, $studiensemester_kurzbz=NULL)
    {
	$this->db->distinct();
	
	if(is_null($studiensemester_kurzbz))
	{
	    $this->db->select("p.person_id, p.zugangscode, p.insertamum")
		->from("public.tbl_person p")
		->join("public.tbl_kontakt k", "p.person_id=k.person_id")
		->join("public.tbl_benutzer b", "p.person_id=b.person_id", "left")
		->where("k.kontakttyp", 'email')
		->where("(kontakt='".$email."'".
			" OR alias ||'@technikum-wien.at'='".$email."'".
			" OR uid ||'@technikum-wien.at'='".$email."')")
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
		->where("(kontakt='".$email."'".
			" OR alias ||'@technikum-wien.at'='".$email."'".
			" OR uid ||'@technikum-wien.at'='".$email."')")
		->where("studiensemester_kurzbz='".$studiensemester_kurzbz."'")
		->order_by("p.insertamum", "DESC")
		->limit(1)
		;
	}
	return $this->db->get()->result_array();
    }
    
    public function checkZugangscodePerson($code)
    {
	$this->db->select("p.person_id")
		->from("public.tbl_person p")
		->where("p.zugangscode", $code);
	return $this->db->get()->result_array();
    }
    
    public function updatePerson($person)
    {
	//TODO check berechtigung
//	if ($this->fhc_db_acl->bb->isBerechtigt('person', 'sui'))
//	{
	//TODO set other columns to be updated
	    $this->db->set("zugangscode", $person["zugangscode"]);
	    $this->db->where("person_id", $person["person_id"]);
	    if($this->db->update("public.tbl_person")){
		return true;
	    }
	    else
	    {
		return false;
	    }
//	}
//	else
//	{
//	    return "Nicht berechtigt";
//	}
    }
}
