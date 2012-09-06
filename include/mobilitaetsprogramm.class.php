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
    public $sichtbar_outgoing;


	public function getAll($sichtbar = false)
	{
		$qry ="Select * From bis.tbl_mobilitaetsprogramm ";
		
		if($sichtbar == true)
			$qry.="where sichtbar = 'true'"; 
		
        $qry.=';';
        
		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$mobility = new mobilitaetsprogramm(); 
				
				$mobility->mobilitaetsprogramm_code = $row->mobilitaetsprogramm_code; 
				$mobility->kurzbz = $row->kurzbz; 
				$mobility->beschreibung = $row->beschreibung; 
				$mobility->sichtbar = $this->db_parse_bool($row->sichtbar); 
				
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
    

    /**
     * Lädt ein Mobilitätsprogramm
     * @param int $mobilitaetsprogramm_code
     * @return boolean 
     */
    public function load($mobilitaetsprogramm_code)
    {
        $qry ="SELECT * FROM bis.tbl_mobilitaetsprogramm where mobilitaetsprogramm_code =".$this->db_add_param($mobilitaetsprogramm_code, FHC_INTEGER).';';
        if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{

				$this->mobilitaetsprogramm_code = $row->mobilitaetsprogramm_code; 
				$this->kurzbz = $row->kurzbz; 
				$this->beschreibung = $row->beschreibung; 
				$this->sichtbar = $row->sichtbar; 
				
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
	 * Laedt die Mobilitaetsprogramme die einer Firma zugeteilt sind
	 * @param $firma_id
	 * @return boolean
	 */
	public function getFirmaMobilitaetsprogramm($firma_id)
	{
		$qry = "SELECT 
					* 
				FROM 
					bis.tbl_mobilitaetsprogramm 
					JOIN public.tbl_firma_mobilitaetsprogramm USING(mobilitaetsprogramm_code)
				WHERE
					firma_id=".$this->db_add_param($firma_id, FHC_INTEGER).';';
		
		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$mobility = new mobilitaetsprogramm(); 
				
				$mobility->mobilitaetsprogramm_code = $row->mobilitaetsprogramm_code; 
				$mobility->kurzbz = $row->kurzbz; 
				$mobility->beschreibung = $row->beschreibung; 
				$mobility->sichtbar = $this->db_parse_bool($row->sichtbar);
				$mobility->sichtbar_outgoing = $this->db_parse_bool($row->sichtbar_outgoing);  
				
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
