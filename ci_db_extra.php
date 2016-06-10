<?php
trait db_extra
{
	/*
	 * Moved to private to avoid to violate "Strict standards"
	 * This property must be used only in the methods of this trait
	 * and access to it should only be done through the methods of this trait
	 */
	private $db_result = null;

	public function db_query($sql)
	{
		if ($this->db_result=$this->db->simple_query($sql))
			return $this->db_result;
		else
		{
			$this->errormsg.='Abfrage in Datenbank fehlgeschlagen! '.$this->db_last_error();
			return false;
		}
	}

	public function db_fetch_object($result = null, $i=null)
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

	public function db_add_param($var, $type=FHC_STRING, $nullable=true)
	{
		if($var==='' && $type!=FHC_BOOLEAN)
		{
			if($nullable)
				return 'null';
			else
				return "''";
		}

		switch($type)
		{
			case FHC_INTEGER: 
				$var = $this->db_escape($var);
				if(!is_numeric($var) && $var!=='')
					die('Invalid Integer Parameter detected:'.$var);
				$var = $this->db_null_value($var, false);
				break;

			case FHC_BOOLEAN:
				if($var===true)
					$var='true';
				elseif($var===false)
					$var='false';
				elseif($var=='' && $nullable)
					$var = 'null';
				else
					die('Invalid Boolean Parameter detected');
				break;

			case FHC_STRING:
			default: 
				$var = $this->db_escape($var);
				$var = $this->db_null_value($var);
				break;
		}
		return $var;		
	}
	
	public function db_escape($var)
	{
		return pg_escape_string($var);
	}

	public function db_null_value($var, $qoute=true)
	{
		if($qoute)
			return ($var!==''?$this->db_qoute($var):'null');
		else
			return ($var!==''?$var:'null');	
	}
	
	public function db_qoute($var)
	{
		return "'".$var."'";
	}
	
	public function db_parse_bool($var)
	{
		if($var=='t')
			return true;
		elseif($var=='f')
			return false;
		elseif($var=='')
			return '';
		else
			die('Invalid DB Boolean. Wrong DB-Engine?');
	}
	
	/**
	 * Bereitet ein Array von Elementen auf, damit es in der IN-Klausel eines
	 * Select Befehls verwendet werden kann.
	 */
	public function db_implode4SQL($array)
	{
		$string = '';
		foreach($array as $row)
		{
			if($string!='')
				$string.=',';
			$string.=$this->db_add_param($row);
		}
		return $string;
	}
}
