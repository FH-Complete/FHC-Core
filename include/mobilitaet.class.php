<?php
/* Copyright (C) 2016 fhcomplete.org
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
 * Authors: Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>
 */
require_once(dirname(__FILE__).'/basis_db.class.php');

class mobilitaet extends basis_db
{
	public $new;				//  boolean
	public $result = array();	//  mobilitaet Objekt

	//Tabellenspalten
	public $mobilitaet_id; //  integer
	public $mobilitaetstyp_kurzbz; //varchar(32)
	public $prestudent_id; //  integer
	public $studiensemester_kurzbz; // varchar(16)
	public $mobilitaetsprogramm_code; // integer
	public $gsprogramm_id; // integer
	public $firma_id; // bigint
	public $status_kurzbz; // varchar(20)
	public $ausbildungssemester; // smallint
	public $updateamum;			//  timestamp
	public $updatevon;			//  string
	public $insertamum;      	//  timestamp
	public $insertvon;      	//  string

	/**
	 * Konstruktor
	 * @param $mobilitaet_id ID der mobilitaet die geladen werden soll (Default=null)
	 */
	public function __construct($mobilitaet_id=null)
	{
		parent::__construct();

		if(!is_null($mobilitaet_id))
			$this->load($mobilitaet_id);
	}

	/**
	 * Laedt die mobilitaet mit der ID $mobilitaet_id
	 * @param  $mobilitaet_id ID der zu ladenden mobilitaet
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function load($mobilitaet_id)
	{
		//Pruefen ob id eine gueltige Zahl ist
		if(!is_numeric($mobilitaet_id) || $mobilitaet_id == '')
		{
			$this->errormsg = 'mobilitaet_id muss eine Zahl sein';
			return false;
		}

		//Daten aus der Datenbank lesen
		$qry = "SELECT
					*
				FROM
					bis.tbl_mobilitaet
				WHERE
					mobilitaet_id=".$this->db_add_param($mobilitaet_id, FHC_INTEGER, false);

		if(!$this->db_query($qry))
		{
			$this->errormsg = 'Fehler bei einer Datenbankabfrage';
			return false;
		}

		if($row = $this->db_fetch_object())
		{
			$this->mobilitaet_id = $row->mobilitaet_id;
			$this->prestudent_id = $row->prestudent_id;
			$this->studiensemester_kurzbz = $row->studiensemester_kurzbz;
			$this->mobilitaetsprogramm_code = $row->mobilitaetsprogramm_code;
			$this->gsprogramm_id = $row->gsprogramm_id;
			$this->mobilitaetstyp_kurzbz = $row->mobilitaetstyp_kurzbz;
			$this->firma_id = $row->firma_id;
			$this->status_kurzbz = $row->status_kurzbz;
			$this->ausbildungssemester = $row->ausbildungssemester;
			$this->updateamum = $row->updateamum;
			$this->updatevon = $row->updatevon;
			$this->insertamum = $row->insertamum;
			$this->insertvon = $row->insertvon;

			return true;
		}
		else
		{
			$this->errormsg = 'Es ist kein Datensatz mit dieser ID vorhanden';
			return false;
		}

		return true;
	}

	/**
	 * Laedt die mobilitaet eines Prestudenten
	 * @param  $prestudent_id ID des Prestudenten dessen GS geladen werden soll
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function loadPrestudent($prestudent_id)
	{
		//Pruefen ob id eine gueltige Zahl ist
		if(!is_numeric($prestudent_id) || $prestudent_id == '')
		{
			$this->errormsg = 'prestudent_id muss eine Zahl sein';
			return false;
		}

		//Daten aus der Datenbank lesen
		$qry = "SELECT
					*
				FROM
					bis.tbl_mobilitaet
				WHERE
					prestudent_id=".$this->db_add_param($prestudent_id, FHC_INTEGER, false);

		if(!$this->db_query($qry))
		{
			$this->errormsg = 'Fehler bei einer Datenbankabfrage';
			return false;
		}

		while($row = $this->db_fetch_object())
		{
			$obj = new mobilitaet();

			$obj->mobilitaet_id = $row->mobilitaet_id;
			$obj->prestudent_id = $row->prestudent_id;
			$obj->studiensemester_kurzbz = $row->studiensemester_kurzbz;
			$obj->mobilitaetsprogramm_code = $row->mobilitaetsprogramm_code;
			$obj->gsprogramm_id = $row->gsprogramm_id;
			$obj->mobilitaetstyp_kurzbz = $row->mobilitaetstyp_kurzbz;
			$obj->firma_id = $row->firma_id;
			$obj->status_kurzbz = $row->status_kurzbz;
			$obj->ausbildungssemester = $row->ausbildungssemester;
			$obj->updateamum = $row->updateamum;
			$obj->updatevon = $row->updatevon;
			$obj->insertamum = $row->insertamum;
			$obj->insertvon = $row->insertvon;

			$this->result[] = $obj;
		}

		return true;
	}

	/**
	 * Prueft die Variablen auf Gueltigkeit
	 * @return true wenn ok, false im Fehlerfall
	 */
	protected function validate()
	{
		//Zahlenfelder pruefen
		if(!is_numeric($this->prestudent_id))
		{
			$this->errormsg='prestudent_id enthaelt ungueltige Zeichen';
			return false;
		}
		if(!is_numeric($this->ausbildungssemester))
		{
			$this->errormsg = 'Ausbildungssemester muss eine gueltige Zahl sein';
			return false;
		}

		$this->errormsg = '';
		return true;
	}

	/**
	 * Speichert den aktuellen Datensatz in die Datenbank
	 * Wenn $neu auf true gesetzt ist wird ein neuer Datensatz angelegt
	 * andernfalls wird der Datensatz mit der ID in $mobilitaet_id aktualisiert
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function save()
	{
		//Variablen pruefen
		if(!$this->validate())
			return false;

		if($this->new)
		{
			//Neuen Datensatz einfuegen
			$qry='BEGIN;INSERT INTO bis.tbl_mobilitaet (prestudent_id,
				studiensemester_kurzbz, mobilitaetsprogramm_code, gsprogramm_id, mobilitaetstyp_kurzbz, firma_id,
				status_kurzbz, ausbildungssemester, insertamum, insertvon, updateamum, updatevon) VALUES('.
				$this->db_add_param($this->prestudent_id, FHC_INTEGER).', '.
				$this->db_add_param($this->studiensemester_kurzbz).', '.
				$this->db_add_param($this->mobilitaetsprogramm_code, FHC_INTEGER).', '.
				$this->db_add_param($this->gsprogramm_id, FHC_INTEGER).', '.
				$this->db_add_param($this->mobilitaetstyp_kurzbz).', '.
				$this->db_add_param($this->firma_id, FHC_INTEGER).', '.
				$this->db_add_param($this->status_kurzbz).', '.
				$this->db_add_param($this->ausbildungssemester, FHC_INTEGER).', now(),'.
				$this->db_add_param($this->insertvon).', now(), '.
				$this->db_add_param($this->updatevon).');';
		}
		else
		{
			//Pruefen ob mobilitaet_id eine gueltige Zahl ist
			if(!is_numeric($this->mobilitaet_id))
			{
				$this->errormsg = 'mobilitaet_id muss eine gueltige Zahl sein';
				return false;
			}
			$qry='UPDATE bis.tbl_mobilitaet SET'.
				' prestudent_id='.$this->db_add_param($this->prestudent_id, FHC_INTEGER).', '.
				' studiensemester_kurzbz='.$this->db_add_param($this->studiensemester_kurzbz).', '.
				' mobilitaetsprogramm_code='.$this->db_add_param($this->mobilitaetsprogramm_code).', '.
				' gsprogramm_id='.$this->db_add_param($this->gsprogramm_id).', '.
				' mobilitaetstyp_kurzbz='.$this->db_add_param($this->mobilitaetstyp_kurzbz).', '.
				' firma_id='.$this->db_add_param($this->firma_id).', '.
				' status_kurzbz='.$this->db_add_param($this->status_kurzbz).', '.
				' ausbildungssemester='.$this->db_add_param($this->ausbildungssemester).', '.
				' updateamum= now(), '.
				' updatevon='.$this->db_add_param($this->updatevon).' '.
				'WHERE mobilitaet_id='.$this->db_add_param($this->mobilitaet_id, FHC_INTEGER, false).';';
		}

		if($this->db_query($qry))
		{
			if($this->new)
			{
				//naechste ID aus der Sequence holen
				$qry="SELECT currval('bis.tbl_mobilitaet_mobilitaet_id_seq') as id;";
				if($this->db_query($qry))
				{
					if($row = $this->db_fetch_object())
					{
						$this->mobilitaet_id = $row->id;
						$this->db_query('COMMIT');
					}
					else
					{
						$this->db_query('ROLLBACK');
						$this->errormsg = "Fehler beim Auslesen der Sequence";
						return false;
					}
				}
				else
				{
					$this->db_query('ROLLBACK');
					$this->errormsg = 'Fehler beim Auslesen der Sequence';
					return false;
				}
			}

		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern des Adress-Datensatzes';
			return false;
		}
		return $this->mobilitaet_id;
	}

	/**
	 * Loescht den Datenensatz mit der ID die uebergeben wird
	 * @param $mobilitaet_id ID die geloescht werden soll
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function delete($mobilitaet_id)
	{
		//Pruefen ob mobilitaet_id eine gueltige Zahl ist
		if(!is_numeric($mobilitaet_id) || $mobilitaet_id == '')
		{
			$this->errormsg = 'mobilitaet_id muss eine gültige Zahl sein'."\n";
			return false;
		}

		//loeschen des Datensatzes
		$qry="DELETE FROM bis.tbl_mobilitaet WHERE mobilitaet_id=".$this->db_add_param($mobilitaet_id, FHC_INTEGER, false).";";

		if($this->db_query($qry))
		{
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Löschen der Daten'."\n";
			return false;
		}
	}

	/**
	 * Laedt die vorhandenen Mobilitaetstypen
	 * @param boolean $aktiv gibt an ob nur aktive eintraege geladen werden sollen.default=true
	 * @return boolean truen wenn ok, false im Fehlerfall
	 */
	public function getMobilitaetstyp($aktiv=true)
	{
		$qry = "SELECT * FROM bis.tbl_mobilitaetstyp";
		if($aktiv)
			$qry .= " WHERE aktiv";
		$qry.=" ORDER BY bezeichnung";

		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$obj = new stdClass();

				$obj->mobilitaetstyp_kurzbz = $row->mobilitaetstyp_kurzbz;
				$obj->bezeichnung = $row->bezeichnung;
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
