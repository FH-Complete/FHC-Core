<?php
class Geschaeftsjahr_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'public.tbl_geschaeftsjahr';
		$this->pk = 'geschaeftsjahr_kurzbz';
	}

	/**
	 * Gets latest Geschaeftsjahr, as determined by its start date
	 * @return array|null
	 */
	public function getLastGeschaeftsjahr()
	{
		$query = 'SELECT * 
					FROM public.tbl_geschaeftsjahr
					WHERE start = (
									SELECT max(start) 
									FROM public.tbl_geschaeftsjahr
								  )';

		return $this->execQuery($query);
	}
}
