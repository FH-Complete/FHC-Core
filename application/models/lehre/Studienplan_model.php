<?php

class Studienplan_model extends DB_Model
{
	/**
	 * 
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'lehre.tbl_studienplan';
		$this->pk = 'studienplan_id';
	}
	
/*	public function getStudienplaene($studiengang_kz)
	{
		$studienplaeneQuery = "SELECT DISTINCT tbl_studienplan.*
								 FROM lehre.tbl_studienplan JOIN lehre.tbl_studienordnung USING(studienordnung_id)
							    WHERE tbl_studienordnung.studiengang_kz = ?";
		
	}*/
}