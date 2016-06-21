<?php

class Dms_model extends DB_Model
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'campus.tbl_dms';
		$this->pk = 'dms_id';
	}
	
	/**
	 * 
	 */
	public function filterFields($dms)
	{
		$fieldsArray = array('oe_kurzbz', 'dokument_kurzbz', 'kategorie_kurzbz');
		$returnArray = array();
		
		foreach ($fieldsArray as $value)
		{
			if (isset($dms[$value]))
			{
				$returnArray[$value] = $dms[$value];
			}
		}
		
		return $returnArray;
	}
}