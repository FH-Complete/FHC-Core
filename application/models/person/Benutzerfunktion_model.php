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
	 * @param string $funktion_kurzbz String with one benutzerfunktionname or array with one or more.
	 * @param string $oe_kurzbz
	 * @param bool $activeoeonly If true, retrieve only active Organisationseinheiten.
	 * @param bool $activebenonly If true, retrieve only active Benutzer.
	 * @param bool $oerecursive If true, retrieve all oes under given oe_kurzbz recursively.
	 * @return array|null
	 */
	public function getBenutzerFunktionen($funktion_kurzbz, $oe_kurzbz = null, $activeoeonly = false, $activebenonly = false, $oerecursive = false)
	{
		$parametersArray = array();

		$query = "SELECT * FROM public.tbl_benutzerfunktion";

		if ($activeoeonly === true)
			$query .= " JOIN public.tbl_organisationseinheit USING(oe_kurzbz)";

		if ($activebenonly === true)
			$query .= " JOIN public.tbl_benutzer USING(uid)";

		$query .= " WHERE (datum_von <= NOW() OR datum_von IS NULL) AND (datum_bis >= NOW() OR datum_bis IS NULL)";

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
			if ($oerecursive === true)
			{
				$query .=
				" AND oe_kurzbz IN
				  (
					WITH RECURSIVE oes(oe_kurzbz, oe_parent_kurzbz, aktiv) as
					(
					  SELECT oe_kurzbz, oe_parent_kurzbz, aktiv FROM public.tbl_organisationseinheit
					  WHERE oe_kurzbz=?";

				if ($activeoeonly === true)
					$query .= " AND aktiv=true";

				$query .=
					  " UNION ALL
					  SELECT o.oe_kurzbz, o.oe_parent_kurzbz, o.aktiv FROM public.tbl_organisationseinheit o, oes
					  WHERE o.oe_parent_kurzbz=oes.oe_kurzbz
					)
					SELECT oe_kurzbz
					FROM oes";
				if ($activeoeonly === true)
				{
					$query .= " WHERE aktiv=true";
				}
				$query .= " GROUP BY oe_kurzbz)";
			}
			else
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

		$query .= " ORDER BY oe_kurzbz, funktion_kurzbz, uid";

		return $this->execQuery($query, $parametersArray);
	}
}
