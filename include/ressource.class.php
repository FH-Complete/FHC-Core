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
 * Authors: Karl Burkhart <burkhart@technikum-wien.at>.
 */
 
require_once(dirname(__FILE__).'/basis_db.class.php');

class ressource extends basis_db
{
	public $new;       		// boolean
	public $result = array(); 	// adresse Objekt

	//Tabellenspalten
	public $ressource_id;    	//integer
	public $bezeichnung;	    //string
	public $beschreibung;	    //string
	public $mitarbeiter_uid;	//string
	public $student_uid;	    //string
	public $betriebsmittel_id;	//integer 	
	public $firma_id;		    //integer 	
	public $insertamum;	   	 	//timestamp
	public $insertvon;	    	//string
	public $updateamum;	    	//timestamp
	public $updatevon;	    	//string


	/**
	 * Konstruktor
	 * @param $ressource_id ID der Ressource, die geladen werden soll (Default=null)
	 */
	public function __construct($ressource_id=null)
	{
		parent::__construct();

		if($ressource_id != null) 	
			$this->load($ressource_id);
	}

	/**
	 * Laedt die Ressource mit der ID $ressource_id
	 * @param  $ressource_id ID der zu ladenden Ressource
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function load($ressource_id)
	{
		if(!is_numeric($ressource_id))
		{
			$this->errormsg = 'Ressource_id muss eine gueltige Zahl sein';
			return false;
		}
		
		$qry = "SELECT * FROM fue.tbl_ressource WHERE ressource_id='".addslashes($ressource_id)."'";
		
		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$this->ressource_id = $row->ressource_id;
				$this->bezeichnung = $row->bezeichnung;
				$this->beschreibung = $row->beschreibung;
				$this->mitarbeiter_uid = $row->mitarbeiter_uid;
				$this->student_uid = $row->student_uid;
				$this->betriebsmittel_id = $row->betriebsmittel_id;
				$this->firma_id = $row->firma_id;
				$this->insertamum = $row->insertamum;
				$this->insertvon = $row->insertvon;
				$this->updateamum = $row->updateamum;
				$this->updatevon = $row->updatevon;
				return true;
			}
			else 
			{
				$this->errormsg = 'Datensatz wurde nicht gefunden';
				return false;
			}
		}
		else 
		{
			$this->errormsg = 'Fehler beim Laden der Daten';
			return false;
		}
	}

	/**
	 * Laedt alle Ressourcen
	 * @param $projekt_kurzbz, wenn null -> werden alle ressourcen geladen 
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function getAllRessourcen()
	{
		$qry = "SELECT * FROM fue.tbl_ressource order by ressource_id";
			
		$this->result=array();
			
		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$obj = new ressource();
				
				$obj->ressource_id = $row->ressource_id;
				$obj->bezeichnung = $row->bezeichnung;
				$obj->beschreibung = $row->beschreibung;
				$obj->mitarbeiter_uid = $row->mitarbeiter_uid;
				$obj->student_uid = $row->student_uid;
				$obj->betriebsmittel_id = $row->betriebsmittel_id;
				$obj->firma_id = $row->firma_id;
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;
				$obj->updateamum = $row->updateamum;
				$obj->updatevon = $row->updatevon;
				$this->result[] = $obj;
			}
			//var_dump($this->result);
			return true;
		}
		else 
		{
			$this->errormsg = 'Fehler beim Laden der Daten';
			return false;
		}
	}
	
	public function getProjectRessourcen($project_kurzbz)
	{
		$qry = "SELECT ressource.* FROM fue.tbl_ressource as ressource
		JOIN fue.tbl_projekt_ressource project ON(project.projekt_ressource_id = ressource.ressource_id) 
		WHERE project.projekt_kurzbz ='".addslashes($projekt_kurzbz)."';";
		
		$this->result=array();
			
		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$obj = new ressource();
				
				$obj->ressource_id = $row->ressource_id;
				$obj->bezeichnung = $row->bezeichnung;
				$obj->beschreibung = $row->beschreibung;
				$obj->mitarbeiter_uid = $row->mitarbeiter_uid;
				$obj->student_uid = $row->student_uid;
				$obj->betriebsmittel_id = $row->betriebsmittel_id;
				$obj->firma_id = $row->firma_id;
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;
				$obj->updateamum = $row->updateamum;
				$obj->updatevon = $row->updatevon;
				$this->result[] = $obj;
			}
			//var_dump($this->result);
			return true;
		}
		else 
		{
			$this->errormsg = 'Fehler beim Laden der Daten';
			return false;
		}
	}
	
}
?>