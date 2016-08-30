<?php

class Gemeinde_model extends DB_Model
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = "bis.tbl_gemeinde";
		$this->pk = "gemeinde_id";
	}
	
	public function getGemeindeByPlz($plz)
	{
		$this->addSelect("DISTINCT ON (ortschaftsname) ortschaftsname, gemeinde_id, plz, name, ortschaftskennziffer, bulacode, bulabez, kennziffer");
		$this->addOrder("ortschaftsname");
		
		return $this->loadWhere(array("plz" => $plz));
	}
}