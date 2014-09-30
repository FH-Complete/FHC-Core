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

class buchung extends basis_db
{
	public $new;
	public $result = array();
	
	public $buchungstyp_kurzbz;
	public $buchungstyp_bezeichnung;
        
        /**
	 * Konstruktor
	 */
	public function __construct()
	{
		parent::__construct();
	}
	
	/**
	 * Speichert den Buchungstyp in der Datenbank
	 * Wenn $new auf true gesetzt ist wird ein neuer Datensatz
	 * angelegt, ansonsten der Datensatz upgedated
	 * @return true wenn erfolgreich, false im Fehlerfall
	 */
	public function saveBuchungstyp($new=null)
	{
		if(is_null($new))
			$new = $this->new;
		
		if($this->buchungstyp_kurzbz=='')
		{
			$this->errormsg = 'Buchungstyp_kurzbz muss angegeben werden';
			return false;
		}

		if($new)
		{
			//Prüfung, ob Eintrag bereits vorhanden
			$qry='SELECT buchungstyp_kurzbz FROM wawi.tbl_buchungstyp
				WHERE buchungstyp_kurzbz='.$this->db_add_param($this->buchungstyp_kurzbz);
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
			$qry = 'INSERT INTO wawi.tbl_buchungstyp(buchungstyp_kurzbz, bezeichnung) VALUES('.
			        $this->db_add_param($this->buchungstyp_kurzbz).','.
			        $this->db_add_param($this->buchungstyp_bezeichnung).');';
		}
		else
		{
			$qry = 'UPDATE wawi.tbl_buchungstyp SET '.
					'bezeichnung = '.$this->db_add_param($this->buchungstyp_bezeichnung).
					'WHERE buchungstyp_kurzbz = '.$this->db_add_param($this->buchungstyp_kurzbz);
		}
		
		if($this->db_query($qry))
		{
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern des Buchungstyps';
			return false;
		}
	}

	/**
	 * Loescht einen Buchungstyp wenn er noch nicht verwendet wird
	 * @param buchungstyp_kurzbz
	 */
	public function deleteBuchungstyp($buchungstyp_kurzbz)
	{
		// prüfen ob Buchungstyp bereits verwenet wird
		$qry = "SELECT buchungstyp_kurzbz FROM wawi.tbl_buchung
			WHERE buchungstyp_kurzbz = " . $this->db_add_param($buchungstyp_kurzbz);
		
		if($this->db_query($qry))
		{
			if($this->db_fetch_object())
			{
				$this->errormsg = "Der Buchungstyp kann nicht gelöscht werden da er bereits verwendet wird";
				return false;
			}
		}
		else
		{
			$this->errormsg = "Fehler beim Durchführen der Datenbankabfrage";
			return false;
		}
		
		if(is_null($buchungstyp_kurzbz))
		{
			$this->errormsg = 'Buchungstyp_kurzbz darf nicht leer sein';
			return false;
		}
		
		$qry = "DELETE FROM wawi.tbl_buchungstyp 
				WHERE buchungstyp_kurzbz=".$this->db_add_param($buchungstyp_kurzbz).";";
		
		if($this->db_query($qry))
			return true;
		else 	
		{
			$this->errormsg = 'Fehler beim Loeschen des Buchungstyps';
			return false;
		}
	}
	
	/**
	 * Liefert alle Buchungstypen
	 * @return true wenn ok false im Fehlerfall
	 */
	public function getAllBuchungstypen()
	{
		$qry = "SELECT * FROM wawi.tbl_buchungstyp ORDER BY bezeichnung;";
		
		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$buchung = new buchung();
                                
                                $buchung->buchungstyp_kurzbz = $row->buchungstyp_kurzbz;
                                $buchung->buchungstyp_bezeichnung = $row->bezeichnung;
                            
                                $this->result[] = $buchung;
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
	 * Laedt einen Buchungstyp
	 *
	 * @param $buchungstyp_kurzbz
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function loadBuchungstyp($buchungstyp_kurzbz)
	{
		$qry="SELECT * FROM wawi.tbl_buchungstyp
				WHERE
					buchungstyp_kurzbz=".$this->db_add_param($buchungstyp_kurzbz);

		if($result = $this->db_query($qry))
		{
			if($row = $this->db_fetch_object($result))
			{
				$this->buchungstyp_kurzbz = $row->buchungstyp_kurzbz;
				$this->buchungstyp_bezeichnung = $row->bezeichnung;
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
