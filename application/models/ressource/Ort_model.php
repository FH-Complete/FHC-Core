<?php

class Ort_model extends DB_Model
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = "public.tbl_ort";
		$this->pk = "ort_kurzbz";
	}
	
	public function getAll($raumtyp_kurzbz)
	{
		$this->addOrder("ort_kurzbz");
		
		$this->addJoin("public.tbl_ortraumtyp", "ort_kurzbz");
		
		return $this->OrtModel->loadWhere(array("raumtyp_kurzbz" => $raumtyp_kurzbz));
	}
}