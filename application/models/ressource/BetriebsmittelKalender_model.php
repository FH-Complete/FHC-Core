<?php
class BetriebsmittelKalender_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'lehre.tbl_betriebsmittel_kalender';
		$this->pk = 'betriebsmittel_kalender_id';
	}
}
