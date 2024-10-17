<?php

class RtStudienplan_model extends DB_Model
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = "public.tbl_rt_studienplan";
		$this->pk = array("rt_id", "studienplan_id");
		$this->hasSequence = false;
	}
}
