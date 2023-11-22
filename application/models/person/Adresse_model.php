<?php
class Adresse_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'public.tbl_adresse';
		$this->pk = 'adresse_id';
	}


	/**
	 * Get Zustelladress of given person.
	 * @param string $person_id
	 * @param string $select
	 * @return array
	 */
	public function getZustellAdresse($person_id, $select = '*')
	{
		$this->addSelect($select);
		return $this->loadWhere(array('person_id' => $person_id, 'zustelladresse'=> true));
	}

	/**
	 * Get Array of Names of Gemeinden having Postleitzahl
	 * @param integer $plz Postleitzahl
	 * @return array $result[]
	 */
	public function getGemeinden($plz)
	{
		$qry = "
			SELECT distinct
				g.name
			FROM bis.tbl_gemeinde g
			WHERE g.plz=?
			ORDER BY g.name
			";

		return $this->execQuery($qry, array($plz));
	}

	/**
	 * Get Array of Names of Ortschaften having Postleitzahl
	 * @param integer $plz Postleitzahl
	 * @param String $gemeindename Name Gemeinde
	 * @return array $result[]
	 */
	public function getOrtschaften($plz, $gemeinde=null)
	{
		$params = array($plz);
		$qry = "
			SELECT distinct
				g.ortschaftsname
			FROM bis.tbl_gemeinde g
			WHERE g.plz=? ";

		if (isset($gemeinde))
		{
			$qry .= "AND g.name=?";
			$params[] = $gemeinde;
		}

		$qry .=	"ORDER BY g.ortschaftsname";

		return $this->execQuery($qry, $params);
	}
}



