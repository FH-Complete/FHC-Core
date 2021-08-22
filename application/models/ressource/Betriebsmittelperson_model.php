<?php
class Betriebsmittelperson_model extends DB_Model
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'wawi.tbl_betriebsmittelperson';
		$this->pk = 'betriebsmittelperson_id';
	}
	
	/**
	 * Get Betriebsmittel by person.
	 * @param string $person_id
	 * @param string $betriebsmitteltyp
	 * @param bool $isRetourniert   False to retrieve only active Betriebsmittel.
	 * @return array|bool
	 */
	public function getBetriebsmittel($person_id, $betriebsmitteltyp = null, $isRetourniert = null)
	{
		if (!is_numeric($person_id))
		{
			$this->errormsg = 'Person_id type is not valid.';
			return false;
		}
		
		$this->addJoin('wawi.tbl_betriebsmittel', 'betriebsmittel_id');
		
		$condition = '
			person_id = '. $this->escape($person_id). '
		';
		
		if (is_string($betriebsmitteltyp)) {
			$condition .= '
			 AND betriebsmitteltyp = ' . $this->escape($betriebsmitteltyp);
		}
		
		if ($isRetourniert === true) {
			$condition .= '
			  AND retouram IS NOT NULL';    //  return date is given
		}
		elseif ($isRetourniert === false)
		{
			$condition .= '
			  AND retouram IS NULL';    // default
		}
		
		$this->addOrder('ausgegebenam', 'DESC');   //  default
		
		return $this->loadWhere($condition);
	}
}
