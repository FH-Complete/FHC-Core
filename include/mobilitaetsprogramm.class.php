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
 * Klasse Mobilitaetsprogramm
 */
 
require_once(dirname(__FILE__).'/basis_db.class.php');

class mobilitaetsprogramm extends basis_db 
{
	public $new;      			//  boolean
	public $result = array(); 	//  email Objekt
	
	//Tabellenspalten
	public $mobilitaetsprogramm_code;
	public $kurzbz;
	public $beschreibung;
	public $sichtbar; 


	public function getAll($sichtbar = false)
	{
		$qry ="Select * From bis.tbl_mobilitaetsprogramm ";
		
		if($sichtbar == true)
			$qry.="where sichtbar = 'true';"; 
		
		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$mobility = new mobilitaetsprogramm(); 
				
				$mobility->mobilitaetsprogramm_code = $row->mobilitaetsprogramm_code; 
				$mobility->kurzbz = $row->kurzbz; 
				$mobility->beschreibung = $row->beschreibung; 
				$mobility->sichtbar = $row->sichtbar; 
				
				$this->result[]=$mobility; 
			}
			return true; 
		}
		else
		{
			$this->errormsg = "Fehler bei der Abfrage aufgetreten"; 
			return false; 
		}
	}

}
