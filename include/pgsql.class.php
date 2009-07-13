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
		if ($this->db_result=@pg_query(basis_db::$db_conn,$sql))
			return $this->db_result;
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
	
	function db_fetch_row($result = null, $i=null)
	{
		if(is_null($result))
		{
			if(is_null($i))
				return pg_fetch_row($this->db_result);
			else 
				return pg_fetch_row($this->db_result, $i);
		}
		else 
		{
			if(is_null($i))
				return pg_fetch_row($result);
			else 
				return pg_fetch_row($result, $i);
		}			
	}
	
	function db_result($result = null, $i,$item)
	{
		if(is_null($result))
		{
			return pg_result($this->db_result, $i,$item);
		}
		else 
		{
			return pg_result($result, $i,$item);
		}			
	}
	
	function db_last_error()
	{
		return pg_last_error();
	}
	
	function db_affected_rows($result=null)
	{
		if(is_null($result))
			return pg_affected_rows($this->db_result);
		else
			return pg_affected_rows($result);
	}
	
	function db_fetch_array($result=null)
	{
		if(is_null($result))
			return pg_fetch_array($this->db_result);
		else
			return pg_fetch_array($result);
	}
	
	function db_num_fields($result=null)
	{
		if(is_null($result))
			return pg_num_fields($this->db_result);
		else
			return pg_num_fields($result);
	}
	
	function db_field_name($result=null, $i)
	{
		if(is_null($result))
			return pg_field_name($this->db_result, $i);
		else
			return pg_field_name($result, $i);
	}

	function db_free_result($result = null)
	{
		if(is_null($result))
		{
			return pg_free_result($this->db_result);
		}
		else 
		{
			return pg_free_result($result);
		}			
	}	
}
?>