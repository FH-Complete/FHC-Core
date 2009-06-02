<?php

class basis_db extends db
{
	function db_connect()
	{
		$conn_str='host='.DB_HOST.' port='.DB_PORT.' dbname='.DB_NAME.' user='.DB_USER.' password='.DB_PASSWORD;
		//Connection Herstellen
		if (DB_CONNECT_PERSISTENT)
		{
			if(!basis_db::$db_conn = pg_pconnect($conn_str))
				die('Fehler beim Oeffnen der Datenbankverbindung');
		}
		else
		{
			if(!basis_db::$db_conn = pg_connect($conn_str))
				die('Fehler beim Oeffnen der Datenbankverbindung');
		}
	}

	function db_query($sql)
	{
		//echo $sql.'<BR/>';
		if ($this->db_result=pg_query(basis_db::$db_conn,$sql))
			return true;
		else
		{
			$this->errormsg.='Abfrage in Datenbank fehlgeschlagen! '.$this->db_last_error();
			return false;
		}
	}

	function db_num_rows($result=null)
	{
		if(is_null($result))
			return pg_num_rows($this->db_result);
		else
			return pg_num_rows($result);
	}

	function db_fetch_object($result = null, $i=null)
	{
		if(is_null($result))
		{
			if(is_null($i))
				return pg_fetch_object($this->db_result);
			else 
				return pg_fetch_object($this->db_result, $i);
		}
		else 
		{
			if(is_null($i))
				return pg_fetch_object($result);
			else 
				return pg_fetch_object($result, $i);
		}			
	}

	function db_last_error()
	{
		return pg_last_error();
	}
}
?>