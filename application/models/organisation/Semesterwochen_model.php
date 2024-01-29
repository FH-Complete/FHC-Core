<?php
class Semesterwochen_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'public.tbl_semesterwochen';
		$this->pk = array('studiengang_kz', 'semester');
	}
}
