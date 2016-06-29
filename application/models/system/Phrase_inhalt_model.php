<?php
class Phrase_inhalt_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'system.tbl_phrase_inhalt';
		$this->pk = 'phrase_inhalt_id';
	}

}
