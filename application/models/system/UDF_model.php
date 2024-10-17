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
	 * Returns all the UDF for this table
	 */
	public function getUDFsDefinitions($schemaAndTable)
	{
		$st = $this->getSchemaAndTable($schemaAndTable);

		$this->addSelect(UDFLib::COLUMN_JSON_DESCRIPTION);
		$udfResults = $this->loadWhere(
			array(
				'schema' => $st->schema,
				'table' => $st->table
			)
		);

		return $udfResults;
	}
}
