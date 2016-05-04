<?php
class Kontakt_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'public.tbl_kontakt';
		$this->pk = 'kontakt_id';
	}

	public function saveKontakt($kontakt)
    {
	//TODO check berechtigung
//	if ($this->fhc_db_acl->bb->isBerechtigt('person', 'sui'))
//	{
	    $data = array(
		"person_id"=>$kontakt["person_id"],
		"kontakttyp"=>$kontakt["kontakttyp"],
		"kontakt"=>$kontakt["kontakt"],
		"insertvon"=>$kontakt["insertvon"],
		"insertamum"=>date('Y-m-d H:i:s')
	    );
	    if($this->db->insert("public.tbl_kontakt", $data)){
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
    
    public function getKontaktPerson($person_id)
    {
	$this->db->select("*")
		->from("public.tbl_kontakt k")
		->where("k.person_id", $person_id);
	
	return $this->db->get()->result_array();
    }
}
