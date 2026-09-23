<?php
class Benutzergruppe_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'public.tbl_benutzergruppe';
		$this->pk = array('gruppe_kurzbz', 'uid');
		$this->hasSequence = false;
	}
	
	/**
	 * Laedt die User in einer Benutzergruppe
	 * @param gruppe_kurzbz, stsem
	 * @return array
	 */
	public function getUids($gruppe_kurzbz, $stsem)
	{
		$query = "
			SELECT 
				uid 
			FROM 
				public.tbl_benutzergruppe 
			WHERE 
				gruppe_kurzbz = " . $this->escape($gruppe_kurzbz) . " 
				AND studiensemester_kurzbz = " . $this->escape($stsem);
		
		$res = $this->execReadOnlyQuery($query);
		$uids = (hasData($res)) ? getData($res) : array();
		return $uids;
	}

	/**
	 * Laedt die Aufnahmegruppe(n) in Abhängigkeit von User und Studiensemester
	 * @param uid, gruppe_kurzbz, studiensemester_kurzbz
	 * @return array
	 */
	public function loadAufnahmegruppen($uid, $stsem)
	{
		$query = "
				SELECT * FROM tbl_gruppe WHERE aufnahmegruppe=true;";
		return $this->execReadOnlyQuery($query);
	}
}
