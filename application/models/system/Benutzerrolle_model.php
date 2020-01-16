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

	/**
	 * Get user who are authorized with berechtigung and, if given, authorized for the specific organisational unit.
	 * @param $berechtigung_kurzbz
	 * @param null $oe_kurzbz
	 * @return array
	 */
	public function getBenutzerByBerechtigung($berechtigung_kurzbz, $oe_kurzbz = null)
	{
		$params = array();
		$query = '
			SELECT
				*
			FROM
				system.vw_berechtigung_nichtrekursiv
			WHERE
				berechtigung_kurzbz = ?';

		$params[] = $berechtigung_kurzbz;

		if (!is_null($oe_kurzbz))
		{
			$query .= ' AND oe_kurzbz = ?';
			$params[] = $oe_kurzbz;
		}

		return $this->execQuery($query, $params);
	}
}
