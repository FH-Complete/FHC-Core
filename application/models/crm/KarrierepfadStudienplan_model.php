<?php

class KarrierepfadStudienplan_model extends DB_Model
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'public.tbl_karrierepfad_studienplan';
		$this->pk = array('karrierepfad_id', 'studienplan_id');
		$this->hasSequence = false;
	}
}
