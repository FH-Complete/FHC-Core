<?php
/* Copyright (C) 2011 FH Technikum-Wien
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307, USA.
 *
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>,
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> 
 *
 */
/**
 * Datenbank Abstraktionsklasse fuer Postgresql Datenbank
 */

class basis_db extends db
{
	public function db_connect()
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

	public function db_query($sql)
	{
		if ($this->db_result=pg_query(basis_db::$db_conn,$sql))
			return $this->db_result;
		else
		{
			$this->errormsg.='Abfrage in Datenbank fehlgeschlagen! '.$this->db_last_error();
			return false;
		}
	}

	public function db_num_rows($result=null)
	{
		if(is_null($result))
			return pg_num_rows($this->db_result);
		else
			return pg_num_rows($result);
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
	
	public function db_fetch_row($result = null, $i=null)
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
	
	public function db_result($result = null, $i,$item)
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
	
	public function db_last_error()
	{
		return pg_last_error();
	}
	
	public function db_affected_rows($result=null)
	{
		if(is_null($result))
			return pg_affected_rows($this->db_result);
		else
			return pg_affected_rows($result);
	}
	
	public function db_fetch_array($result=null)
	{
		if(is_null($result))
			return pg_fetch_array($this->db_result);
		else
			return pg_fetch_array($result);
	}
	
	public function db_num_fields($result=null)
	{
		if(is_null($result))
			return pg_num_fields($this->db_result);
		else
			return pg_num_fields($result);
	}
	
	/**
	 * Liefert den Feldnamen mit index i
	 */
	public function db_field_name($result=null, $i)
	{
		if(is_null($result))
			return pg_field_name($this->db_result, $i);
		else
			return pg_field_name($result, $i);
	}

	/**
	 * Gibt den Speicher wieder Frei.
	 * (ist das sinnvoll wenn es per Value uebergeben wird??)
	 */
	public function db_free_result($result = null)
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
	
	/**
	 * Liefert die aktuelle Datenbankversion
	 */
	public function db_version()
	{
		return pg_version(basis_db::$db_conn);
	}

	/**
	 * Escaped Sonderzeichen in Variablen vor der Verwendung in SQL Statements
	 * um SQL Injections zu verhindern
	 * 
	 */
	public function db_escape($var)
	{
		return pg_escape_string($var);
	}

	/**
	 * Null Value Handling und Hochkomma für Inserts / Updates
	 * Wenn die Uebergebe Variable leer ist, wird ein String mit null
	 * zurueckgeliefert, wenn nicht dann wird der string unter Hochkomma zurueckgeliefert
	 * es sei denn qoute=false dann wird nur der String zurueckgeliefert
	 *
	 * @param $var String-Value fuer SQL Request
	 * @return string
	 */
	public function db_null_value($var, $qoute=true)
	{
		if($qoute)
			return ($var!=''?$this->db_qoute($var):'null');
		else
			return ($var!=''?$var:'null');		
	}

	/**
	 * Setzt einen String unter Hochkomma
	 * @param $var Value fuer Insert/Update
	 * @return value unter Hochkomma
	 */
	public function db_qoute($var)
	{
		return "'".$var."'";
	}

	/**
	 * Escaped einen Parameter fuer die Verwendung in Insert/Update SQL Befehlen
     * Es werden abhaengig vom Typ Hochkomma oder Null hinzugefuegt
	 * @param $var Value der gesetzt werden soll
	 * @param $type Typ des Values (FHC_STRING | FHC_BOOLEAN | FHC_INTEGER | ...)
	 * @param $nullable boolean gibt an ob das Feld NULL sein darf. Wenn true wird 
	 *                  NULL statt einem Leerstring zurueckgeliefert
	 * @return Escapter Value inklusive Hochkomma wenn noetig
	 * 
     * Verwendungsbeispiel:
	 *	Update tbl_person set nachname=$this->db_add_param($var)
	 *  Update tbl_person set aktiv=$this->db_add_param($var, FHC_BOOL, false)
     *	Update tbl_person set anzahlkinder=$this->db_add_param($var, FHC_INT)
	 */
	public function db_add_param($var, $type=FHC_STRING, $nullable=true)
	{
		if($var=='' && $type!=FHC_BOOLEAN)
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
				if(!is_numeric($var))
					die('Invalid Integer Parameter detected');
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

	/**
	 * Erzeugt aus einem DB-Result-Boolean einen PHP Boolean
	 */
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

}
?>
