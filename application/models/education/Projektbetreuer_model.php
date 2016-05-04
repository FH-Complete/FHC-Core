<?php
class Projektbetreuer_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'lehre.tbl_projektbetreuer';
		$this->pk = array('betreuerart_kurzbz', 'projektarbeit_id', 'person_id');
	}
}
