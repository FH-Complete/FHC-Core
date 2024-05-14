<?php
/* Copyright (C) 2010 Technikum-Wien
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
 * Authors:	Karl Burkhart <burkhart@technikum-wien.at>.
 */
 
require_once(dirname(__FILE__).'/basis_db.class.php');

class webservicelog extends basis_db
{
	public $webservicelog_id;		// Serial
	public $webservicetyp_kurzbz;	// FK varchar(32)
	public $request_id;				// varchar(64)
	public $beschreibung;			// varchar(256)
	public $request_data;			// text
	public $execute_time;			// timestampt
	public $execute_user;			// varchar(32)

	public $new;					// boolean
	public $result = array(); 		// webservicelog object array
	
	/**
	 * Konstruktor - Laedt optional einen DS
	 * @param $log_id
	 */
	public function __construct($webservicelog_id=null)
	{
		parent::__construct();

		if(!is_null($webservicelog_id))
			$this->load($webservicelog_id);
	}
	
	/**
	 * 
	 * Lädt den Log mit übergebener ID
	 * @param $webservicelog_id
	 */
	public function load($webservicelog_id)
	{
		if(!is_numeric($webservicelog_id))
		{
			$this->errormsg ="Ungültige ID übergeben.";
			return false; 
		}
		
		$qry ="SELECT * FROM system.tbl_webservicelog WHERE 
			webservicelog_id =".$this->db_add_param($webservicelog_id, FHC_INTEGER, false).";";
		
		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$this->webservicelog_id = $row->webservicelog_id; 
				$this->webservicetyp_kurzbz = $row->webservicetyp_kurzbz; 
				$this->request_id = $row->request_id; 
				$this->beschreibung = $row->beschreibung; 
				$this->request_data = $row->request_data; 
				$this->execute_time = $row->execute_time; 
				$this->execute_user = $row->execute_user; 
				return true; 
			}
			else
			{
				$this->errormsg = "Kein Log mit der ID gefunden.";
				return false; 
			}
		}
		else
		{
			$this->errormsg ="Fehler bei der Abfrage aufgetreten.";
			return false; 
		}
	}
	
	/**
	 * 
	 * Speichert den aktuellen Log in der Datenbank
	 * @param $new
	 */
	public function save($new = null)
	{
		if(is_null($new))
			$new = $this->new; 
			
		// insert
		$qry ="INSERT INTO system.tbl_webservicelog(webservicetyp_kurzbz, request_id, beschreibung, 
					request_data, execute_time, execute_user) VALUES (".
					$this->db_add_param($this->webservicetyp_kurzbz).",".
					$this->db_add_param($this->request_id).",".
					$this->db_add_param($this->beschreibung).",".
					$this->db_add_param($this->request_data).",now(),".
					$this->db_add_param($this->execute_user).");";

		if($this->db_query($qry))
		{
			return true; 
		}
		else
		{
			$this->errormsg = "Fehler bei Insert/Update aufgetreten.";
			return false; 
		}
	}
	
	/**
	 * 
	 * Lädt alle Logeinträge eines bestimmten Typs
	 * @param $kurzbz
	 */
	public function getFromTyp($kurzbz)
	{
		if($kurzbz > 32)
		{
			$this->errormsg = "Ungültige Kurzbz"; 
			return false; 
		}
		
		$qry ="SELECT * FROM system.tbl_webservicelog WHERE 
			webservicetyp_kurzbz =".$this->db_add_param($kurzbz).";";
		
		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$log = new webservicelog(); 
				$log->webservicelog_id = $row->webservicelog_id; 
				$log->webservicetyp_kurzbz = $row->webservicetyp_kurzbz; 
				$log->request_id = $row->request_id; 
				$log->beschreibung = $row->beschreibung; 
				$log->request_data = $row->request_data; 
				$log->execute_time = $row->execute_time; 
				$log->execute_user = $row->execute_user; 
				
				$this->result[] = $log; 
			}
			return true; 
		}
		else
		{
			$this->errormsg = "Fehler bei der Abfrage aufgetreten"; 
			return false; 
		}
	}
	
	/**
	 * 
	 * Validiert Übergabeparameter
	 */
	public function validate()
	{
		if($this->webservicetyp_kurzbz > 32)
		{
			$this->errormsg = "Kurzbz ist zu lang.";
			return false; 
		}
		if($this->request_id > 64)
		{
			$this->errormsg = "RequestID ist zu lang";
			return false; 
		}
		if($this->beschreibung > 256)
		{
			$this->errormsg = "Beschreibung ist zu lang"; 
			return false;
		}
		return true; 
	}
}