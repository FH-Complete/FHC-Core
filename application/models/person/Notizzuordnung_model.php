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
		$validTypes = [];

		$qry = "
			SELECT column_name
			FROM information_schema.columns
			WHERE table_schema = 'public'
			AND table_name   = 'tbl_notizzuordnung'
			AND column_name not in ('notizzuordnung_id', 'notiz_id')
		";

		$type_arr = $this->execQuery($qry);
		$type_arr = $type_arr->retval;

		foreach ($type_arr as $t)
		{
			$validTypes[] = $t->column_name;
		}

		if (in_array($type, $validTypes))
		{
			$result = success('Type of Id is valid');
		}
		else
		{
			$result = error('Type of Id  is not valid');
		}
		return $result;
	}
}
