<?php

class UDF_model extends DB_Model
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'system.tbl_udf';
		$this->pk = array('schema', 'table');
		$this->hasSequence = false;
	}
	
	/**
	 * 
	 */
	public function execQuery($query, $parametersArray = null)
	{
		// 
		if (
			(
				substr($query, 0, 6) == 'SELECT'
				|| substr($query, 0, 4) == 'WITH'
			)
			&&
			(
				!stripos($query, 'INSERT')
				&& !stripos($query, 'UPDATE')
				&& !stripos($query, 'DELETE')
			)
		)
		{
			return parent::execQuery($query, $parametersArray);
		}
		else
		{
			return error('You are allowed to run only query for reading data');
		}
	}
}