<?php
class Person_model extends DB_Model 
{
	public function __construct($uid=null)
	{
		parent::__construct($uid);
		$this->dbTable='public.tbl_person';
	}

	public function getPerson($person_id = null)
	{
		    if (is_null($person_id))
		    {
		            $query = $this->db->get_where('public.tbl_person', array('vorname' => 'Christian'));
		            return $query->result_object();
		    }

		    $query = $this->db->get_where('public.tbl_person', array('person_id' => $person_id));
		    return $query->row_object();
	}

	public function getPersonByCode($code)
	{
		if ($this->fhc_db_acl->bb->isBerechtigt('person','s'))
		{
			$query = $this->db->get_where('public.tbl_person', array('zugangscode' => $code));
		    return $query->result_object();
		}
	}

	/**
	 * Laedt Personendaten eine BenutzerUID
	 * @param	string	$uid	DB-Attr: tbl_benutzer.uid .
	 * @return	bool
	 */
	public function getPersonFromBenutzerUID($uid)
	{
		
		if (!$this->fhc_db_acl->bb->isBerechtigt('person','s'))
		{
			$this->db->select('tbl_person.*');
			$this->db->from('public.tbl_person JOIN public.tbl_benutzer USING (person_id)');
			$query = $this->db->get_where(null, array('uid' => $uid));
		    return $query->result_object();
		}
	}
}
