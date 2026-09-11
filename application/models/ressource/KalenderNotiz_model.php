<?php
class KalenderNotiz_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'lehre.tbl_kalender_notiz';
		$this->pk = array('eindeutige_kalender_gruppen_id', 'notiz_id');
	}
}
