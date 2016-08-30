<?php

class Dokumentstudiengang_model extends DB_Model
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = "public.tbl_dokumentstudiengang";
		$this->pk = array("studiengang_kz", "dokument_kurzbz");
	}
	
	public function getDokumentstudiengangByStudiengang_kz($studiengang_kz, $onlinebewerbung, $pflicht)
	{
		// Checks if the operation is permitted by the API caller
		if (($chkRights = $this->isEntitled("public.tbl_dokument", "s", FHC_NORIGHT, FHC_MODEL_ERROR)) !== true)
			return $chkRights;
		
		$this->addJoin("public.tbl_dokument", "dokument_kurzbz");
			
		$parameterArray = array("studiengang_kz" => $studiengang_kz);
	
		if( isset($onlinebewerbung))
		{
			$parameterArray["onlinebewerbung"] = $onlinebewerbung;
		}

		if( isset($pflicht))
		{
			$parameterArray["pflicht"] = $pflicht;
		}
		
		return $this->loadWhere($parameterArray);
	}
}