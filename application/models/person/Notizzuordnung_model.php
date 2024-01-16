<?php
class Notizzuordnung_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'public.tbl_notizzuordnung';
		$this->pk = 'notizzuordnung_id';
	}

	public function isValidType($type)
	{
		//var_dump($type);
		$validTypes = [];

		$qry = "
			SELECT column_name
			FROM information_schema.columns
			WHERE table_schema = 'public'
			AND table_name   = 'tbl_notizzuordnung'
		";

		$type_arr = $this->execQuery($qry);
		$type_arr = $type_arr->retval;

		foreach ($type_arr as $t) {
			$validTypes[] = $t->column_name;
		}

		if (in_array($type, $validTypes))
		{
		//	var_dump($type . " is IN ARRAY");
			return true;
		}
		else
		{
			//var_dump($type . " is NOT IN ARRAY");
			return false;
		}
	}
}
