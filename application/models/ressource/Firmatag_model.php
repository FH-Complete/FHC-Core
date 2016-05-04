<?php
class Firmatag_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'public.tbl_firmatag';
		$this->pk = array('tag', 'firma_id');
	}
}
