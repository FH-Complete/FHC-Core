<?php
class Betriebsmittel_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'wawi.tbl_betriebsmittel';
		$this->pk = 'betriebsmittel_id';
	}

	/**
	 * getBetriebsmittelById
	 */
/*	public function loadBetriebsmittel($betriebsmittelperson_id){
		$query = "
			SELECT *
			FROM wawi.tbl_betriebsmittelperson
			JOIN wawi.tbl_betriebsmittel ON (wawi.tbl_betriebsmittelperson.betriebsmittel_id = wawi.tbl_betriebsmittel.betriebsmittel_id)
			WHERE wawi.tbl_betriebsmittelperson.betriebsmittelperson_id  = ? 
		";

		return $this->execQuery($query, array($betriebsmittelperson_id));
	}*/

}
