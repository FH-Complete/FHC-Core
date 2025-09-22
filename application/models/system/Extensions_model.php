<?php

class Extensions_model extends DB_Model
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct('system');
		$this->dbTable = 'system.tbl_extensions';
		$this->pk = 'extension_id';
	}

	/**
	 * getDependencies
	 */
	public function getDependencies($dependencies)
	{
		return $this->execQuery(
			'SELECT *
			   FROM '.$this->dbTable.'
			  WHERE enabled = TRUE
				AND name IN ?',
			array('name' => $dependencies)
        );
	}

	/**
	 *
	 */
	public function executeQuery($sql)
	{
		return $this->execQuery($sql);
	}
}

