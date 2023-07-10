<?php
/* Copyright (C) 2011 fhcomplete.org
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
 * Authors: Christopher Gerbrich <christopher.gerbrich@technikum-wien.at> and Manuela Thamer <manuela.thamer@technikum-wien.at
 */
/**
 * Klasse Studierendenantrag
 *  
 */
require_once(dirname(__FILE__).'/basis_db.class.php');

class studierendenantrag extends basis_db
{
	public $new;
	public $result = array();

	//Tabellenspalten
	public $studierendenantrag_id;		// bigint
	public $prestudent_id;				// bigint
	public $studiensemester_kurzbz;		// varchar(32)
	public $datum;						// timestamp
	public $typ;						// varchar(32)
	public $insertamum;					// timestamp
	public $insertvon;					// varchar(32)
	public $datum_wiedereinstieg;		// timestamp
	public $grund;						// text
	public $dms_id;						// bigint
	 
	/**
	 * Konstruktor - Laedt optional eine Ampel
	 * @param $amepl_id
	 */
	public function __construct($studierendenantrag_id=null)
	{
		parent::__construct();
		
		if(!is_null($studierendenantrag_id))
			$this->load($studierendenantrag_id);
	}

	/**
	 * Laedt einen Antrag mit der uebergebenen ID
	 * 
	 * @param $studierendenantrag_id
	 * @return boolean
	 */
	public function load($studierendenantrag_id)
	{
		if(!is_numeric($studierendenantrag_id))
		{
			$this->errormsg = 'Studierendenantrag ID ist ungueltig';
			return false;
		}
		
		$qry = "SELECT * FROM campus.tbl_studierendenantrag WHERE studierendenantrag_id=" . $this->db_add_param($studierendenantrag_id, FHC_INTEGER);

		if($result = $this->db_query($qry))
		{
			if($row = $this->db_fetch_object($result))
			{
				$this->studierendenantrag_id = $row->studierendenantrag_id;
				$this->prestudent_id = $row->prestudent_id;
				$this->studiensemester_kurzbz = $row->studiensemester_kurzbz;
				$this->datum = $row->datum;
				$this->typ = $row->typ;
				$this->insertamum = $row->insertamum;
				$this->insertvon = $row->insertvon;
				$this->datum_wiedereinstieg = $row->datum_wiedereinstieg;
				$this->grund = $row->grund;
				$this->dms_id = $row->dms_id;

				return true;
			}
			else
			{
				$this->errormsg = 'Studierendenantrag mit dieser ID exisitert nicht';
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Studierendenantrag';
			return false;
		}
	}
	
	/**
	 * Laedt alle aktuellen Anträge eines Users
	 * 
	 * @param integer	$prestudent_id
	 * @param string 	$stsem
	 * 
	 * @return boolean
	 */
	public function loadUserAntrag($prestudent_id, $stsem = null)
	{
		$qry = "SELECT * FROM campus.tbl_studierendenantrag WHERE typ IN ('Abmeldung','AbmeldungStgl','Unterbrechung') AND campus.get_status_studierendenantrag(studierendenantrag_id)='Genehmigt' AND prestudent_id=" . $this->db_add_param($prestudent_id, FHC_INTEGER);
		
		if ($stsem)
		{
			$qry .= " AND studiensemester_kurzbz=" . $this->db_add_param($stsem, FHC_STRING);
		}

		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$obj = new studierendenantrag();
				
				$obj->studierendenantrag_id = $row->studierendenantrag_id;
				$obj->prestudent_id = $row->prestudent_id;
				$obj->studiensemester_kurzbz = $row->studiensemester_kurzbz;
				$obj->datum = $row->datum;
				$obj->typ = $row->typ;
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;
				$obj->datum_wiedereinstieg = $row->datum_wiedereinstieg;
				$obj->grund = $row->grund;
				$obj->dms_id = $row->dms_id;
				
				$this->result[] = $obj;
			}
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
