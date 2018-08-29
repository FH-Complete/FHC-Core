<?php

class Benutzerfunktion_model extends DB_Model
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'public.tbl_benutzerfunktion';
		$this->pk = 'benutzerfunktion_id';
	}
	
	/**
	 * Get the Benutzerfunktion using the person_id
	 */
	public function getByPersonId($person_id)
	{
		// Join with the table 
		$this->addJoin('public.tbl_benutzer', 'uid');
		
		return $this->loadWhere(array('person_id' => $person_id));
	}

	/**
	 * Gets all Benutzer for a given OE and specified Benutzerfunktionen
	 * @param $oe_kurzbz
	 * @param $funktion_kurzbz string with one benutzerfunktionname or array with one or more
	 * @return array|null
	 */
	public function getBenutzerFunktionen($funktion_kurzbz, $oe_kurzbz = null, $activeoeonly = false, $activebenonly = false)
	{
		$parametersArray = array();

		$query = "SELECT * FROM public.tbl_benutzerfunktion";

		if ($activeoeonly === true)
			$query .= " JOIN public.tbl_organisationseinheit USING(oe_kurzbz)";

		if ($activebenonly === true)
			$query .= " JOIN public.tbl_benutzer USING(uid)";

		$query .= "WHERE (datum_von <= NOW() OR datum_von IS NULL) AND (datum_bis >= NOW() OR datum_bis IS NULL)";

		if (is_string($funktion_kurzbz))
		{
			$query .= " AND funktion_kurzbz = ".$funktion_kurzbz.")";
		}
		elseif (is_array($funktion_kurzbz) && count($funktion_kurzbz) > 0)
		{
			$funktionstr = "'".implode("', '", $funktion_kurzbz)."'";
			$query .= " AND funktion_kurzbz IN (".$funktionstr.")";
		}

		if (is_string($oe_kurzbz))
		{
			$query .= " AND tbl_benutzerfunktion.oe_kurzbz = ?";
			$parametersArray[] = $oe_kurzbz;
		}

		if ($activebenonly === true)
		{
			$query .= " AND tbl_benutzer.aktiv";
		}

		if ($activeoeonly === true)
		{
			$query .= " AND tbl_organisationseinheit.aktiv";
		}

		$query .= " ORDER BY funktion_kurzbz, oe_kurzbz, uid";

		return $this->execQuery($query, $parametersArray);
	}
}
