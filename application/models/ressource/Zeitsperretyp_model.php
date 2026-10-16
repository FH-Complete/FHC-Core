<?php
class Zeitsperretyp_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'campus.tbl_zeitsperretyp';
		$this->pk = 'zeitsperretyp_kurzbz';
	}

	public function getTypenZeitsperren($excludedTypes = [])
	{
		$whereNotClause = "";
		if (count($excludedTypes)) {
			$excludedTypes = array_map(
				function ($type) {
					return "'$type'";
				},
				$excludedTypes
			);
			$excludedTypes = implode(", ", $excludedTypes);
			$whereNotClause = "WHERE NOT zeitsperretyp_kurzbz IN ($excludedTypes)";
		}
		return $this->execQuery("SELECT * FROM $this->dbTable $whereNotClause ORDER BY beschreibung ASC");
	}
}
