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
 * Klasse Organisationsform
 */
 
class organisationsform extends basis_db
{
	public $orgform_kurzbz; 
	public $code; 
	public $bezeichnung; 
	public $rolle; 
	
	public $result = array(); 
	
	/**
	 * 
	 * Konstruktor 
	 */
	public function __construct() 
	{
		parent::__construct(); 		
	}
	
	/**
	 * 
	 * Liefert alle Organisationsformen zurück
	 */
	public function getAll()
	{
		$qry = "Select * from bis.tbl_orgform"; 
		
		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$orgform = new organisationsform(); 
				
				$orgform->orgform_kurzbz = $row->orgform_kurzbz; 
				$orgform->code = $row->code; 
				$orgform->bezeichnung = $row->bezeichnung; 
				$orgform->rolle = $row->rolle; 
				
				$result[] = $orgform; 
			}
		}
		else
		{
			$this->errormsg ="Fehler bei der Abfrage aufgetreten"; 
			return false; 
		}
	}

 /**
  * 
  * Orgform Kurzbezeichnung wird übergeben und alle passenden Kurzbezeichnungen werden zurückgegeben
  * @param $orgform_kurzbz
  */
	
	public function checkOrgForm($orgform_kurzbz)
	{	

		if(is_null($orgform_kurzbz))
		{
			$this->errormsg ="Kein gültiger Wert für Orgform Kurzbz."; 
			return false; 
		}
		
		switch ($orgform_kurzbz) 
		{
			case "VZ":
				$vzArray= array('VZ', '');
				return $vzArray; 
			case "BB":
				$bbArray=array('BB','DL','DDP','');
				return $bbArray; 
			case "VBB":
				$vbbArray = array('VZ','BB','DDP','DL');
				return $vbbArray; 
			default:
				return false; 
		}
	}
}
