<?php
class Firma_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'public.tbl_firma';
		$this->pk = 'firma_id';
	}
}
