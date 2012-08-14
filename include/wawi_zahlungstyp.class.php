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
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>,
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *          Karl Burkhart <burkhart@technikum-wien.at>.
 */
/**
 * Klasse WaWi Zahlungstyp
 */
 
require_once(dirname(__FILE__).'/basis_db.class.php');

class wawi_zahlungstyp extends basis_db
{
	public $new; 				// boolean
	public $result = array(); 	// object array
	
	public $zahlungstyp_kurzbz; // varchar
	public $bezeichnung; 		// varchar
	
	/**
	 * 
	 * Konstruktor - Laedt optional einen Zahlungstyp
	 * @param $zahlungstyp_kurzbz
	 */
	public function __construct($zahlungstyp_kurzbz=null)
	{
		parent::__construct();
		
		if(!is_null($zahlungstyp_kurzbz))
			$this->load($zahlungstyp_kurzbz);
	}
	
	/**
	 * 
	 * Lädt den Datensatz mit der übergebenen kurzbz
	 * @param $zahlungstyp_kurzbz
	 */
	public function load($zahlungstyp_kurzbz)
	{
		if($zahlungstyp_kurzbz == '')
		{
			$this->errormsg ='Ungueltige Zahlungstypkurzbezeichnung'; 
			return false; 
		}
		
		$qry ="SELECT * FROM wawi.tbl_zahlungstyp WHERE zahlungstyp_kurzb = ".$this->db_add_param($zahlungstyp_kurzbz).';'; 
		
		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$this->zahlungstyp_kurzbz = $row->zahlungstyp_kurzbz; 
				$this->bezeichnung = $row->bezeichnung; 
			}
			return true; 
		}
		else 
		{
			$this->errormsg = "Datenbankabfrage fehlgeschlagen"; 
			return false; 
		}
		
	}
	
	/**
	 * 
	 * Laedt alle Zahlungstypen
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function getAll()
	{
		$qry = "SELECT * FROM wawi.tbl_zahlungstyp ORDER by zahlungstyp_kurzbz;"; 
		
		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$typ = new wawi_zahlungstyp(); 
				$typ->zahlungstyp_kurzbz = $row->zahlungstyp_kurzbz; 
				$typ->bezeichnung = $row->bezeichnung; 
				
				$this->result[] = $typ; 
			}
			return true; 
		}
		else
		{
			$this->errormsg = 'Datenbankabfrage fehlgeschlagen'; 
			return false; 
		}
	}	
}