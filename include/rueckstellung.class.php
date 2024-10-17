<?php

require_once(dirname(__FILE__).'/basis_db.class.php');
require_once(dirname(__FILE__).'/sprache.class.php');

class rueckstellung extends basis_db
{
	
	public function __construct()
	{
		parent::__construct();
	}
	
	/**
	 * Löscht alle Einträge mit dem Status "parked" von der übergebenen Person_id, die in der Zukunft liegen.
	 * @param $person_id
	 * @return bool
	 */
	public function deleteParked($person_id)
	{
		$qry = "DELETE
				FROM public.tbl_rueckstellung
				WHERE person_id =  ".$this->db_add_param($person_id)."
					AND status_kurzbz = 'parked'
					AND datum_bis >= now();";
		
		if($this->db_query($qry))
		{
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Löschen des geparkten Eintrages';
			return false;
		}
	}
}
?>
