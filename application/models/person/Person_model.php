<?php
class Person_model extends DB_Model 
{
	public function __construct($uid=null)
	{
		parent::__construct($uid);
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
}
