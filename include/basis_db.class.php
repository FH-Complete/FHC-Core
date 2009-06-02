<?php
require_once('basis.class.php');

abstract class db extends basis
{
	protected static $db_conn=null;
	protected $db_result=null;

	function __construct()
	{
		if (is_null(db::$db_conn))
			$this->db_connect();
	}

	abstract function db_connect();
	abstract function db_query($sql);
	abstract function db_fetch_object($result=null, $i=null);
	abstract function db_num_rows($result=null);
	abstract function db_last_error();

}

require_once(DB_SYSTEM.'.class.php');

?>