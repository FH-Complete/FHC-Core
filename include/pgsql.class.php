<?php

class basis_db extends db
{
	function db_connect()
	{
		$conn_str=CONN_STRING;
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
		if ($this->db_result=pg_query(basis_db::$db_conn,$sql))
			return true;
		else
		{
			$this->errormsg='Abfrage in Datenbank fehlgeschlagen! '.$this->db_lasterror();
			return false;
		}
	}

	function db_fetch_object()
	{
		return pg_fetch_object($this->db_result);
	}
}
?>