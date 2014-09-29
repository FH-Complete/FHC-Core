<?php
/* Copyright (C) 2014 fhcomplete.org
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
 * Authors: Nikolaus Krondraf <nikolaus.krondraf@technikum-wien.at>
 */
require_once(dirname(__FILE__).'/basis_db.class.php');

class vertrag extends basis_db
{
	public $new;
	public $result = array();
	
	public $vertragtyp_kurzbz;
	public $vertragtyp_bezeichnung;
        
        /**
	 * Konstruktor
	 */
	public function __construct()
	{
		parent::__construct();
	}
	
	/**
	 * Speichert den Vertragstyp in der Datenbank
	 * Wenn $new auf true gesetzt ist wird ein neuer Datensatz
	 * angelegt, ansonsten der Datensatz upgedated
	 * @return true wenn erfolgreich, false im Fehlerfall
	 */
	public function saveVertragtyp($new=null)
	{
		if(is_null($new))
			$new = $this->new;
		
		if($this->vertragtyp_kurzbz=='')
		{
			$this->errormsg = 'Vertragtyp_kurzbz muss angegeben werden';
			return false;
		}

		if($new)
		{
			//Prüfung, ob Eintrag bereits vorhanden
			$qry='SELECT vertragstyp_kurzbz FROM lehre.tbl_vertragstyp
				WHERE vertragstyp_kurzbz='.$this->db_add_param($this->vertragtyp_kurzbz);
			if($this->db_query($qry))
			{
				if($this->db_fetch_object())
				{
					$this->errormsg = 'Eintrag bereits vorhanden';
					return false;
				}
			}
			else
			{
				$this->errormsg = 'Fehler beim Durchführen der Datenbankabfrage';
				return false;
			}
		}

		if($new)
		{
			$qry = 'INSERT INTO lehre.tbl_vertragstyp(vertragstyp_kurzbz, bezeichnung) VALUES('.
			        $this->db_add_param($this->vertragtyp_kurzbz).','.
			        $this->db_add_param($this->vertragtyp_bezeichnung).');';
		}
		else
		{
			$qry = 'UPDATE lehre.tbl_vertragstyp SET '.
					'bezeichnung = '.$this->db_add_param($this->vertragtyp_bezeichnung).
					'WHERE vertragstyp_kurzbz = '.$this->db_add_param($this->vertragtyp_kurzbz);
		}
		
		if($this->db_query($qry))
		{
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern des Vertragstyps';
			return false;
		}
	}

	/**
	 * Loescht einen Vertragstyp
	 * @param vertragtyp_kurzbz
	 */
	public function deleteVertragtyp($vertragtyp_kurzbz)
	{
		if(is_null($vertragtyp_kurzbz))
		{
			$this->errormsg = 'Vertragtyp_kurzbz darf nicht leer sein';
			return false;
		}
		
		$qry = "DELETE FROM lehre.tbl_vertragstyp 
				WHERE vertragstyp_kurzbz=".$this->db_add_param($vertragtyp_kurzbz).";";
		
		if($this->db_query($qry))
			return true;
		else 	
		{
			$this->errormsg = 'Fehler beim Loeschen des Vertragstyps';
			return false;
		}
	}
	
	/**
	 * Liefert alle Vertragsytpen
	 * @return true wenn ok false im Fehlerfall
	 */
	public function getAllVertragtypen()
	{
		$qry = "SELECT * FROM lehre.tbl_vertragstyp ORDER BY bezeichnung;";
		
		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$vertrag = new vertrag();
				
				$vertrag->vertragtyp_kurzbz = $row->vertragstyp_kurzbz;
				$vertrag->vertragtyp_bezeichnung = $row->bezeichnung;
				
				$this->result[] = $vertrag;
			}
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Daten';
			return false;
		}
	}
	
	/**
	 * Laedt einen Vertragstyp
	 *
	 * @param $vertragtyp_kurzbz
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function loadVertragtyp($vertragtyp_kurzbz)
	{
		$qry="SELECT * FROM lehre.tbl_vertragstyp 
				WHERE
					vertragstyp_kurzbz=".$this->db_add_param($vertragtyp_kurzbz);

		if($result = $this->db_query($qry))
		{
			if($row = $this->db_fetch_object($result))
			{
				$this->vertragtyp_kurzbz = $row->vertragstyp_kurzbz;
				$this->vertragtyp_bezeichnung = $row->bezeichnung;
				return true;
			}
			else
			{
				$this->errormsg='Fehler beim Laden der Daten';
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Daten';
			return false;
		}				
	}  
}
?>
