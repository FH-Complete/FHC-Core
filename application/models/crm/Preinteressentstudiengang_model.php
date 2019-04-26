<?php
class Preinteressentstudiengang_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'public.tbl_preinteressentstudiengang';
		$this->pk = array('preinteressent_id', 'studiengang_kz');
	}
}
