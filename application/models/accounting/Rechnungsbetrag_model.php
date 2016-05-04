<?php
class Rechnungsbetrag_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'wawi.tbl_rechnungsbetrag';
		$this->pk = 'rechnungsbetrag_id';
	}
}
