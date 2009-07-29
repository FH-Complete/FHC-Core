<?php
require_once(dirname(__FILE__).'/basis.class.php');

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
	abstract function db_fetch_array($result=null);
	abstract function db_fetch_row($result=null, $i=null);
	abstract function db_result($result = null, $i,$item);
	abstract function db_num_rows($result=null);
	abstract function db_num_fields($result=null);
	abstract function db_field_name($result=null, $i);
	abstract function db_affected_rows($result=null);
	abstract function db_last_error();
	abstract function db_free_result($result=null);	
	abstract function db_version();
}

require_once(dirname(__FILE__).'/'.DB_SYSTEM.'.class.php');
 
?>