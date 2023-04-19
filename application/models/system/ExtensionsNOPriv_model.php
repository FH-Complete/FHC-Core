<?php

class ExtensionsNOPriv_model extends DB_Model
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->dbTable = 'system.tbl_extensions';
		$this->pk = 'extension_id';
		parent::__construct();
	}

	/**
	 *
	 */
	public function getInstalledExtensions()
	{
		$query = 'SELECT extension_id, e1.name, e1.version, description, license, url, core_version, dependencies, enabled
					FROM system.tbl_extensions e1
			  INNER JOIN (
				  SELECT name, MAX(version) AS version
				  	FROM system.tbl_extensions
				GROUP BY name) e2
					  ON (e1.name = e2.name AND e1.version = e2.version)';

		return $this->execQuery($query);
	}
}

