<?php
class Person_model extends DB_Model 
{
	public function __construct()
	{
		parent::__construct();
	}

	public function getPersonen($person_id = FALSE)
	{
		    if ($person_id === FALSE)
		    {
		            $query = $this->db->get_where('public.tbl_person', array('vorname' => 'Christian'));
		            return $query->result_object();
		    }

		    $query = $this->db->get_where('public.tbl_person', array('person_id' => $person_id));
		    return $query->row_object();
	}

	public function getPersonByCode($code)
	{
		    $query = $this->db->get_where('public.tbl_person', array('zugangscode' => $code));
		    return $query->result_object();
	}
}
