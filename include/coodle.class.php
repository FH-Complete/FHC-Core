<?php
/* Copyright (C) 2012 FH Technikum-Wien
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
/**
 * Klasse Coodle
 */
require_once(dirname(__FILE__).'/basis_db.class.php');

class coodle extends basis_db
{
	public $new;
	public $result = array();

	//Tabellenspalten
	public $coodle_id;				// integer
	public $ersteller_uid;			// varchar(32)
	public $coodle_status_kurzbz;	// varchar(32)
	public $titel; 					// varchar(64)  
	public $beschreibung;			// text
	public $dauer;					// smallint
	public $endedatum;            	// date
	public $updateamum;				// timestamp
	public $updatevon;				// varchar(32)
	public $insertamum;    		  	// timestamp
	public $insertvon;     	 		// varchar(32)

	/**
	 * Konstruktor
	 * @param $coodle_id ID die geladen werden soll (Default=null)
	 */
	public function __construct($coodle_id=null)
	{
		parent::__construct();
		
		if(!is_null($coodle_id))
			$this->load($coodle_id);
	}

	/**
	 * Laedt einen Eintrag mit der ID $coodle_id
	 * @param  $coodle_id ID des zu ladenden Eintrags
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function load($coodle_id)
	{
		//Pruefen ob coodle_id eine gueltige Zahl ist
		if(!is_numeric($coodle_id) || $coodle_id == '')
		{
			$this->errormsg = 'Coodle_id muss eine Zahl sein';
			return false;
		}

		//Daten aus der Datenbank lesen
		$qry = "SELECT * FROM campus.tbl_coodle WHERE coodle_id=".$this->db_add_param($coodle_id, FHC_INTEGER, false);

		if(!$this->db_query($qry))
		{
			$this->errormsg = 'Fehler bei einer Datenbankabfrage';
			return false;
		}

		if($row = $this->db_fetch_object())
		{
			$this->coodle_id = $row->coodle_id;
			$this->ersteller_uid = $row->ersteller_uid;
			$this->coodle_status_kurzbz = $row->coodle_status_kurzbz;
			$this->titel = $row->titel;
			$this->beschreibung = $row->beschreibung;
			$this->dauer = $row->dauer;
			$this->endedatum = $row->endedatum;
			$this->updateamum = $row->updateamum;
			$this->updatevon = $row->updatevon;
			$this->insertamum = $row->insertamum;
			$this->insertvon = $row->insertvon;
		}
		else
		{
			$this->errormsg = 'Es ist kein Datensatz mit dieser ID vorhanden';
			return false;
		}

		return true;
	}

	/**
	 * Prueft die Variablen auf Gueltigkeit
	 * @return true wenn ok, false im Fehlerfall
	 */
	protected function validate()
	{
		//Gesamtlaenge pruefen
		if(mb_strlen($this->coodle_status_kurzbz)>32)
		{
			$this->errormsg = 'Status darf nicht laenger als 32 Zeichen sein';
			return false;
		}
		if(mb_strlen($this->titel)>64)
		{
			$this->errormsg = 'titel darf nicht länger als 64 Zeichen sein';
			return false;
		}
		
		$this->errormsg = '';
		return true;
	}
	
	/**
	 * Speichert den aktuellen Datensatz in die Datenbank
	 * Wenn $neu auf true gesetzt ist wird ein neuer Datensatz angelegt
	 * andernfalls wird der Datensatz mit der ID in $coodle_id aktualisiert
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function save($new = null)
	{
		if(is_null($new))
			$new = $this->new;
		
		//Variablen pruefen
		if(!$this->validate())
			return false;

		if($new)
		{
			//Neuen Datensatz einfuegen
			$qry='BEGIN;INSERT INTO campus.tbl_coodle(ersteller_uid, coodle_status_kurzbz, titel, beschreibung,
				dauer, endedatum, insertamum, insertvon, updateamum, updatevon) VALUES('.
			      $this->db_add_param($this->ersteller_uid).', '.
			      $this->db_add_param($this->coodle_status_kurzbz).', '.
			      $this->db_add_param($this->titel).', '.
			      $this->db_add_param($this->beschreibung).', '.
			      $this->db_add_param($this->dauer).', '.
			      $this->db_add_param($this->endedatum).', '.
			      $this->db_add_param($this->insertamum).', '.
			      $this->db_add_param($this->insertvon).', '.
			      $this->db_add_param($this->updateamum).', '.
			      $this->db_add_param($this->updatevon).');';
		}
		else
		{
			//Pruefen ob coodle_id eine gueltige Zahl ist
			if(!is_numeric($this->coodle_id))
			{
				$this->errormsg = 'coolde_id muss eine gueltige Zahl sein';
				return false;
			}
			$qry='UPDATE campus.tbl_coodle SET'.
				' ersteller_uid='.$this->db_add_param($this->ersteller_uid).', '.
				' coodle_status_kurzbz='.$this->db_add_param($this->coodle_status_kurzbz).', '.
				' titel='.$this->db_add_param($this->titel).', '.
				' beschreibung='.$this->db_add_param($this->beschreibung).', '.
				' dauer='.$this->db_add_param($this->dauer).', '.
		      	' endedatum='.$this->db_add_param($this->endedatum).', '.
		      	' updateamum='.$this->db_add_param($this->updateamum).', '.
		      	' updatevon='.$this->db_add_param($this->updatevon).' '.
		      	'WHERE coodle_id='.$this->db_add_param($this->coodle_id, FHC_INTEGER, false).';';
		}
		
		if($this->db_query($qry))
		{
			if($new)
			{
				//naechste ID aus der Sequence holen
				$qry="SELECT currval('campus.seq_coodle_coodle_id') as id;";
				if($this->db_query($qry))
				{
					if($row = $this->db_fetch_object())
					{
						$this->coodle_id = $row->id;
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
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern des Datensatzes';
			return false;
		}
	}

	/**
	 * Loescht den Datenensatz mit der ID die uebergeben wird
	 * @param $coodle_id ID die geloescht werden soll
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function delete($coodle_id)
	{
		//Pruefen ob adresse_id eine gueltige Zahl ist
		if(!is_numeric($coodle_id) || $coodle_id == '')
		{
			$this->errormsg = 'Coodle_id muss eine gültige Zahl sein'."\n";
			return false;
		}

		//loeschen des Datensatzes
		$qry="DELETE FROM campus.tbl_coodle WHERE coodle_id=".$this->db_add_param($coodle_id, FHC_INTEGER, false).";";

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
}
?>
