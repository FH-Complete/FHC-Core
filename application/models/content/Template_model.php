<?php
class Template_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'campus.tbl_template';
		$this->pk = 'template_kurzbz';
	}
}
