<?php
class Lehrverband_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'public.tbl_lehrverband';
		$this->pk = array('gruppe', 'verband', 'semester', 'studiengang_kz');
	}
}
