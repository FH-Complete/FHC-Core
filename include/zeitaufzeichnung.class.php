<?php
/* Copyright (C) 2006 Technikum-Wien
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
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>.
 */
/**
 * Klasse Zeitaufzeichnung
 * @create 06-11-2007
 */
require_once(dirname(__FILE__).'/basis_db.class.php');

class zeitaufzeichnung extends basis_db
{
	public $new;		// boolean
	public $result = array();	// zeitaufzeichnung Objekt
	public $done=false;		// boolean

	//Tabellenspalten
	public $zeitaufzeichnung_id;	// serial
	public $uid;					// varchar(16)
	public $aktivitaet_kurzbz;		// varchar(16)
	public $start;					// timestamp
	public $ende;					// timestamp
	public $beschreibung;			// varchar(256)
	public $studiengang_kz;			// integer
	public $fachbereich_kurzbz;		// varchar(16)
	public $insertamum;				// timestamp
	public $insertvon;				// varchar(16)
	public $updateamum;				// timestamp
	public $updatevon;				// varchar(16)
	public $projekt_kurzbz;			// varchar(16)
	
	

	/**
	 * Konstruktor
	 * @param $zeitaufzeichnung_id ID der Zeitaufzeichnung die geladen werden soll (Default=null)
	 */
	public function __construct($zeitaufzeichnung_id=null)
	{
		parent::__construct();

		if($zeitaufzeichnung_id != null)
			$this->load($zeitaufzeichnung_id);
	}

	/**
	 * Laedt die Zeitaufzeichnung mit der ID $zeitaufzeichnung_id
	 * @param  $adress_id ID der zu ladenden Adresse
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function load($zeitaufzeichnung_id)
	{
		//Pruefen ob zeitaufzeichnung_id eine gueltige Zahl ist
		if(!is_numeric($zeitaufzeichnung_id) || $zeitaufzeichnung_id == '')
		{
			$this->errormsg = 'Zeitaufzeichnung_id muss eine Zahl sein';
			return false;
		}

		//Daten aus der Datenbank lesen
		$qry = "SELECT * FROM campus.tbl_zeitaufzeichnung WHERE zeitaufzeichnung_id='$zeitaufzeichnung_id'";

		if(!$this->db_query($qry))
		{
			$this->errormsg = 'Fehler bei einer Datenbankabfrage';
			return false;
		}

		if($row = $this->db_fetch_object())
		{
			$this->zeitaufzeichnung_id = $row->zeitaufzeichnung_id;
			$this->uid = $row->uid;
			$this->aktivitaet_kurzbz = $row->aktivitaet_kurzbz;
			$this->start = $row->start;
			$this->ende = $row->ende;
			$this->beschreibung = $row->beschreibung;
			$this->studiengang_kz = $row->studiengang_kz;
			$this->fachbereich_kurzbz = $row->fachbereich_kurzbz;
			$this->insertamum = $row->insertamum;
			$this->insertvon = $row->insertvon;
			$this->updateamum = $row->updateamum;
			$this->updatevon = $row->updatevon;
			$this->projekt_kurzbz = $row->projekt_kurzbz;
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
		$this->errormsg = '';
		return true;
	}

	/**
	 * Speichert den aktuellen Datensatz in die Datenbank
	 * Wenn $neu auf true gesetzt ist wird ein neuer Datensatz angelegt
	 * andernfalls wird der Datensatz mit der ID in $adresse_id aktualisiert
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
			$qry='BEGIN;INSERT INTO campus.tbl_zeitaufzeichnung (uid, aktivitaet_kurzbz, start, ende, beschreibung, 
			      studiengang_kz, fachbereich_kurzbz, insertamum, insertvon, updateamum, updatevon, projekt_kurzbz) VALUES('.
			      $this->addslashes($this->uid).', '.
			      $this->addslashes($this->aktivitaet_kurzbz).', '.
			      $this->addslashes($this->start).', '.
			      $this->addslashes($this->ende).', '.
			      $this->addslashes($this->beschreibung).', '.
			      $this->addslashes($this->studiengang_kz).', '.
			      $this->addslashes($this->fachbereich_kurzbz).','.
			      $this->addslashes($this->insertamum).', '.
			      $this->addslashes($this->insertvon).', '.
			      $this->addslashes($this->updateamum).', '.
			      $this->addslashes($this->updatevon).', '.
			      $this->addslashes($this->projekt_kurzbz).');';
		}
		else
		{
			//Updaten des bestehenden Datensatzes

			//Pruefen ob zeitaufzeichnung_id eine gueltige Zahl ist
			if(!is_numeric($this->zeitaufzeichnung_id))
			{
				$this->errormsg = 'zeitaufzeichnung_id muss eine gueltige Zahl sein: '.$this->zeitaufzeichnung_id."\n";
				return false;
			}
			
			$qry='UPDATE campus.tbl_zeitaufzeichnung SET'.
				' uid='.$this->addslashes($this->uid).', '.
				' aktivitaet_kurzbz='.$this->addslashes($this->aktivitaet_kurzbz).', '.
				' start='.$this->addslashes($this->start).', '.
				' ende='.$this->addslashes($this->ende).', '.
		      	' beschreibung='.$this->addslashes($this->beschreibung).', '.
		      	' studiengang_kz='.$this->addslashes($this->studiengang_kz).', '.
		      	' fachbereich_kurzbz='.$this->addslashes($this->fachbereich_kurzbz).', '.
		      	' updateamum='.$this->addslashes($this->updateamum).', '.
		      	' updatevon='.$this->addslashes($this->updatevon).', '.
		      	' projekt_kurzbz='.$this->addslashes($this->projekt_kurzbz).' '.
		      	'WHERE zeitaufzeichnung_id='.$this->zeitaufzeichnung_id.';';
		}
		
		if($this->db_query($qry))
		{
			if($this->new)
			{
				//naechste ID aus der Sequence holen
				$qry="SELECT currval('campus.tbl_zeitaufzeichnung_zeitaufzeichnung_id_seq') as id;";
				if($this->db_query($qry))
				{
					if($row = $this->db_fetch_object())
					{
						$this->zeitaufzeichnung_id = $row->id;
						$this->db_query('COMMIT');
						return true;
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
			$this->errormsg = 'Fehler beim Speichern';
			return false;
		}
		return true;
	}

	/**
	 * Loescht den Datenensatz mit der ID die uebergeben wird
	 * @param $zeitaufzeichnnung_id ID die geloescht werden soll
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function delete($zeitaufzeichnung_id)
	{
		//Pruefen ob zeitaufzeichnung_id eine gueltige Zahl ist
		if(!is_numeric($zeitaufzeichnung_id) || $zeitaufzeichnung_id == '')
		{
			$this->errormsg = 'zeitaufzeichnung_id muss eine gueltige Zahl sein'."\n";
			return false;
		}

		//loeschen des Datensatzes
		$qry="DELETE FROM campus.tbl_zeitaufzeichnung WHERE zeitaufzeichnung_id='$zeitaufzeichnung_id';";

		if($this->db_query($qry))
		{
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Loeschen der Daten'."\n";
			return false;
		}
	}
}
?>