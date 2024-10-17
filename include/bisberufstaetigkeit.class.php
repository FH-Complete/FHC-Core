<?php
/* Copyright (C) 2006 Technikum-Wien
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
 * Authors: Werner Masik <werner@gefi.at>
 */
require_once(dirname(__FILE__).'/basis_db.class.php');

class bisberufstaetigkeit extends basis_db 
{
	public $new;      //  boolean
	public $result = array(); 
	
	//Tabellenspalten
	public $berufstaetigkeit_code;
	public $berufstaetigkeit_bez;
	public $berufstaetigkeit_kurzbz;
	
	/**
	 * Konstruktor
	 * @param bisverwendung_id ID des zu ladenden Datensatzes
	 */
	public function __construct($berufstaetigkeit_code=null)
	{
		parent::__construct();
		
		if(!is_null($berufstaetigkeit_code))
			$this->load($berufstaetigkeit_code);
	}
	
	/**
	 * Laedt einen Datensatz
	 * @param bisverwendung_id ID des zu ladenden Datensatzes
	 *        studiengang_kz
	 */
	public function load($berufstaetigkeit_code)
	{
		//berufstaetigkeit_code auf gueltigkeit pruefen
		if(!is_numeric($berufstaetigkeit_code) || $berufstaetigkeit_code == '')
		{
			$this->errormsg = 'berufstaetigkeit_code muss eine gueltige Zahl sein';
			return false;
		}
		
		
		//laden des Datensatzes
		$qry = "SELECT * FROM bis.tbl_berufstaetigkeit WHERE berufstaetigkeit_code=".$this->db_add_param($berufstaetigkeit_code, FHC_INTEGER);
		
		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$this->berufstaetigkeit_code = $row->berufstaetigkeit_code;
				$this->berufstaetigkeit_bez = $row->berufstaetigkeit_bez;
				$this->berufstaetigkeit_kurzbz = $row->berufstaetigkeit_kurzbz;
				
				return true;
			}
			else
			{
				$this->errormsg = 'Fehler bei der Datenbankabfrage';
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Fehler bei der Datenbankabfrage';
			return false;
		}
	}
			

	public function getAll()
	{
		//laden des Datensatzes
		$qry = "SELECT * FROM bis.tbl_berufstaetigkeit order by berufstaetigkeit_bez";
		
		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$obj = new bisberufstaetigkeit();				
				$obj->berufstaetigkeit_code = $row->berufstaetigkeit_code;
				$obj->berufstaetigkeit_bez = $row->berufstaetigkeit_bez;
				$obj->berufstaetigkeit_kurzbz = $row->berufstaetigkeit_kurzbz;
			
				$this->result[] = $obj;
			}
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler bei der Datenbankabfrage';
			return false;
		}
	}
	
}
?>