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
	
	public $buchung_id;
	public $konto_id;
	public $kostenstelle_id;
	public $buchungsdatum;	
	public $buchungstext;
	public $betrag;
	public $insertamum;
	public $insertvon;
	public $updateamum;
	public $updatevon;

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
	 * Laedt eine Buchung
	 *
	 * @param $buchung_id ID der Buchung
	 * @return boolean true wenn ok, false im Fehlerfall
	 */
	public function load($buchung_id)
	{
		$qry = "SELECT
					tbl_buchung.* 
				FROM 
					wawi.tbl_buchung 
				WHERE 
					buchung_id=".$this->db_add_param($buchung_id, FHC_INTEGER);

		if($result = $this->db_query($qry))
		{
			if($row = $this->db_fetch_object($result))
			{
				$this->buchung_id = $row->buchung_id;
				$this->konto_id = $row->konto_id;
				$this->kostenstelle_id = $row->kostenstelle_id;
				$this->buchungstyp_kurzbz = $row->buchungstyp_kurzbz;
				$this->buchungsdatum = $row->buchungsdatum;	
				$this->buchungstext = $row->buchungstext;
				$this->betrag = $row->betrag;
				$this->insertamum = $row->insertamum;
				$this->insertvon = $row->insertvon;
				$this->updateamum = $row->updateamum;
				$this->updatevon = $row->updatevon;
				return true;
			}
			else
			{
				$this->errormsg='Es wurde kein Datensatz mit dieser ID gefunden';
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
	 * Prueft die Daten vor dem speichern auf Gueltigkeit
	 * @return true wenn gueltig, false wenn ungueltig
	 */
	public function validate()
	{
		if(mb_strlen($this->buchungstext)>512)
		{
			$this->errormsg = 'Der Buchungstext darf nicht laenger als 512 Zeichen sein';
			return false;
		}
		
		if(mb_strlen($this->betrag)>9)
		{
			$this->errormsg='Betrag ist ungueltig';
			return false;
		}
		if(!is_numeric($this->betrag))
		{
			$this->errormsg='Betrag ist keine Zahl';
			return false;
		}

		return true;
	}

	/**
	 * Speichert eine Buchung in der Datenbank
	 *
	 * @param $new wenn true wird eine neuer Datensatz erstellt
	 * @return boolean true wenn ok, false im Fehlerfall
	 */
	public function save($new=null)
	{
		if(is_null($new))
			$new = $this->new;

		if(!$this->validate())
			return false;
	
		if($new)
		{
			$qry = 'BEGIN;INSERT INTO wawi.tbl_buchung(konto_id, kostenstelle_id, buchungstyp_kurzbz, 
					buchungsdatum, buchungstext, betrag, insertamum, insertvon, updateamum, updatevon) VALUES('.
					$this->db_add_param($this->konto_id, FHC_INTEGER).','.
					$this->db_add_param($this->kostenstelle_id, FHC_INTEGER).','.
					$this->db_add_param($this->buchungstyp_kurzbz).','.
					$this->db_add_param($this->buchungsdatum).','.
					$this->db_add_param($this->buchungstext).','.
					$this->db_add_param($this->betrag).','.
					$this->db_add_param($this->insertamum).','.
					$this->db_add_param($this->insertvon).','.
					$this->db_add_param($this->updateamum).','.
					$this->db_add_param($this->updatevon).');';
		}	
		else
		{
			if($this->buchung_id=='')
			{
				$this->errormsg='BuchungID ist ungueltig';
				return false;
			}
			$qry = 'UPDATE wawi.tbl_buchung SET'.
					' konto_id = '.$this->db_add_param($this->konto_id, FHC_INTEGER).','.
					' kostenstelle_id='.$this->db_add_param($this->kostenstelle_id,FHC_INTEGER).','.
					' buchungstyp_kurzbz='.$this->db_add_param($this->buchungstyp_kurzbz).','.
					' buchungsdatum='.$this->db_add_param($this->buchungsdatum).','.
					' buchungstext='.$this->db_add_param($this->buchungstext).','.
					' betrag='.$this->db_add_param($this->betrag).','.
					' updateamum='.$this->db_add_param($this->updateamum).','.
					' updatevon='.$this->db_add_param($this->updatevon).' '.
					' WHERE buchung_id='.$this->db_add_param($this->buchung_id, FHC_INTEGER,false);
		}
	
		if($this->db_query($qry))
		{
			if($new)
			{
				//Sequence auslesen
				$qry = "SELECT currval('wawi.seq_buchung_buchung_id') as id";
				if($this->db_query($qry))
				{
					if($row = $this->db_fetch_object())
					{
						$this->buchung_id = $row->id;
						$this->db_query('COMMIT');
						return true;
					}
					else 
					{
						$this->errormsg = 'Fehler beim Auslesen der Sequence';
						$this->db_query('ROLLBACK');
						return false;
					}
				}
			}
			else
			{
				return true;
			}
		}
		else
		{
			$this->errromsg = 'Fehler beim Speichern der Daten';
			return false;
		}
	}

	/**
	 * Loescht eine Buchung
	 * 
	 * @param $buchung_id ID der Buchung die entfernt werden soll
	 * @return boolean true wenn ok, false im Fehlerfall
	 */
	public function delete($buchung_id)
	{
		if($buchung_id=='' || !is_numeric($buchung_id))
		{
			$this->errormsg='BuchungID ist ungueltig';
			return false;
		}

		$qry = 'DELETE FROM wawi.tbl_buchung WHERE buchung_id='.$this->db_add_param($buchung_id, FHC_INTEGER, false);
		if($this->db_query($qry))
		{
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Loeschen der Buchung';
			return false;
		}
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

	/**
	 * Laedt die Buchungen einer Person
	 *
	 * @param person_id ID der Person
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function getBuchungPerson($person_id, $options = null)
	{
		// Filter für Query aufbereiten
		$filter = "";
		if(!empty($options['von']))
			$filter .= " AND buchungsdatum >= " . $this->db_add_param($options['von']) . " ";
		
		if(!empty($options['bis']))
			$filter .= " AND buchungsdatum <= " . $this->db_add_param($options['bis']) . " ";
					
		$qry = "SELECT
					tbl_buchung.* 
				FROM 
					wawi.tbl_buchung 
					JOIN wawi.tbl_konto USING(konto_id)
				WHERE 
					tbl_konto.person_id=".$this->db_add_param($person_id, FHC_INTEGER).
					$filter ."
				ORDER BY buchungsdatum";

		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$obj = new buchung();
				$obj->buchung_id = $row->buchung_id;
				$obj->konto_id = $row->konto_id;
				$obj->kostenstelle_id = $row->kostenstelle_id;
				$obj->buchungstyp_kurzbz = $row->buchungstyp_kurzbz;
				$obj->buchungsdatum = $row->buchungsdatum;	
				$obj->buchungstext = $row->buchungstext;
				$obj->betrag = $row->betrag;
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;
				$obj->updateamum = $row->updateamum;
				$obj->updatevon = $row->updatevon;
				
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
