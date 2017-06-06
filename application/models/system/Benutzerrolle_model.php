<?php

class Benutzerrolle_model extends DB_Model
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'system.tbl_benutzerrolle';
		$this->pk = 'benutzerberechtigung_id';
	}
	
	/**
	 * Checks if the given user is an admin
	 */
	public function isAdminByPersonId($person_id)
	{
		// Join with the table tbl_benutzer
		$this->addJoin('public.tbl_benutzer', 'uid');
		
		$result = $this->loadWhere(array('person_id' => $person_id, 'rolle_kurzbz' => 'admin'));
		
		if (!isError($result))
		{
			if (hasData($result))
			{
				$result = success(true);
			}
			else if (!hasData($result))
			{
				$result = success(false);
			}
		}
		
		return $result;
	}
}