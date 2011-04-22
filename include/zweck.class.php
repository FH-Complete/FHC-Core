<?php
/* Copyright (C) 2011 Technikum-Wien
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
 * Klasse Zweck
 */
 
require_once(dirname(__FILE__).'/basis_db.class.php');

class zweck extends basis_db 
{
	public $new;
	public $result = array();
	
	//Tabellenspalten
	public $zweck_code;
	public $kurzbz;
	public $bezeichnung;


	public function getAll()
	{
		$qry ="SELECT * FROM bis.tbl_zweck ORDER BY kurzbz;";
		
		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$obj = new zweck(); 
				
				$obj->zweck_code = $row->zweck_code; 
				$obj->kurzbz = $row->kurzbz; 
				$obj->bezeichnung = $row->bezeichnung; 
				
				$this->result[]=$obj; 
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
