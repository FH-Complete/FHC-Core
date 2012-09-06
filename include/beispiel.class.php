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
require_once(dirname(__FILE__).'/basis_db.class.php');

class beispiel extends basis_db
{
	public $new;					// boolean
	public $beispiele = array();	// lehreinheit Objekt

	//Tabellenspalten
	public $beispiel_id;	// Serial
	public $uebung_id;		// integer
	public $bezeichnung;	// varchar(32)
	public $punkte;			// real
	public $updateamum;		// timestamp
	public $updatevon;		// varchar(16)
	public $insertamum;		// timestamp
	public $insertvon;		// varchar(16)
	public $nummer;			// smallint

	public $student_uid;
	public $vorbereitet;
	public $probleme;

	/**
	 * Konstruktor
	 * @param $beispiel_id
	 */
	public function __construct($beispiel_id=null)
	{
		parent::__construct();
		
		if(!is_null($beispiel_id))
			$this->load($beispiel_id);
	}

	/**
	 * Laedt ein Beispiel
	 * @param beispiel_id
	 */
	public function load($beispiel_id)
	{
		if(!is_numeric($beispiel_id))
		{
			$this->errormsg='Beispiel_id muss eine gueltige Zahl sein';
			return false;
		}
		$qry = "SELECT * FROM campus.tbl_beispiel WHERE beispiel_id=".$this->db_add_param($beispiel_id, FHC_INTEGER).';';

		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$this->beispiel_id = $row->beispiel_id;
				$this->uebung_id = $row->uebung_id;
				$this->punkte = $row->punkte;
				$this->bezeichnung = $row->bezeichnung;
				$this->updateamum = $row->updateamum;
				$this->updatevon = $row->updatevon;
				$this->insertamum = $row->insertamum;
				$this->insertvon = $row->insertvon;
				$this->nummer = $row->nummer;
				return true;
			}
			else
			{
				$this->errormsg = "Es ist kein Beispiel mit der ID $beispiel_id vorhanden";
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden des Beispiels';
			return false;
		}
	}

	/**
	 * Laedt alle Beispiele einer Uebung
	 *
	 * @param $uebung_id
	 * @return boolean
	 */
	public function load_beispiel($uebung_id)
	{
		if(!is_numeric($uebung_id))
		{
			$this->errormsg = 'Uebung_id muss eine gueltige Zahl sein';
			return false;
		}

		$qry = "SELECT * FROM campus.tbl_beispiel WHERE uebung_id=".$this->db_add_param($uebung_id, FHC_INTEGER)." ORDER BY bezeichnung;";

		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$beispiel_obj = new beispiel();

				$beispiel_obj->beispiel_id = $row->beispiel_id;
				$beispiel_obj->uebung_id = $row->uebung_id;
				$beispiel_obj->punkte = $row->punkte;
				$beispiel_obj->bezeichnung = $row->bezeichnung;
				$beispiel_obj->updateamum = $row->updateamum;
				$beispiel_obj->updatevon = $row->updatevon;
				$beispiel_obj->insertamum = $row->insertamum;
				$beispiel_obj->insertvon = $row->insertvon;
				$beispiel_obj->nummer = $row->nummer;

				$this->beispiele[] = $beispiel_obj;
			}
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Beispiele';
			return false;
		}
	}

	/**
	 * Liefert die naechste Nummer
	 *
	 * @return boolean
	 */
	public function get_next_nummer()
	{
		$qry = "SELECT max(nummer) FROM campus.tbl_beispiel;";
		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$this->next_nummer = $row->max + 1;
				return true;
			}
			else 
			{
				$this->errormsg='Fehler beim Ermitteln der Nummer';
				return false;
			}
		}
		else 
		{
			$this->errormsg='Fehler beim Ermitteln der Nummer';
			return false;
		}
	}	
	
	/**
	 * Prueft die Variablen vor dem Speichern
	 * auf Gueltigkeit.
	 * @return true wenn ok, false im Fehlerfall
	 */
	protected function validate()
	{
		if(!is_numeric($this->uebung_id))
		{
			$this->errormsg = 'uebung_id muss eine gueltige Zahl sein';
			return false;
		}
		if(mb_strlen($this->bezeichnung)>32)
		{
			$this->errormsg = 'Bezeichnung darf nicht laenger als 32 Zeichen sein';
			return false;
		}
		return true;
	}
	
	/**
	 * Speichert ein Beispiel in die Datenbank
	 * Wenn $new auf true gesetzt ist wird ein neuer Datensatz
	 * angelegt, ansonsten der Datensatz upgedated
	 * @return true wenn erfolgreich, false im Fehlerfall
	 */
	public function save($new=null)
	{
		if(is_null($new))
			$new = $this->new;

		//Variablen auf Gueltigkeit pruefen
		if(!$this->validate())
			return false;

		if($new)
		{
			if($this->exists($this->uebung_id, $this->bezeichnung))
			{
				$this->errormsg = 'Fehler beim Speichern! Es existiert bereits ein Beispiel mit diesem Namen';
				return false;
			}
			$qry = 'BEGIN; INSERT INTO campus.tbl_beispiel(uebung_id, punkte, bezeichnung, updateamum,
			        updatevon, insertamum, insertvon, nummer) VALUES('.
			        $this->db_add_param($this->uebung_id, FHC_INTEGER).','.
			        $this->db_add_param($this->punkte).','.
			        $this->db_add_param($this->bezeichnung).','.
			        $this->db_add_param($this->updateamum).','.
			        $this->db_add_param($this->updatevon).','.
			        $this->db_add_param($this->insertamum).','.
			        $this->db_add_param($this->insertvon).','.
					$this->db_add_param($this->nummer).');';
		}
		else
		{
			$qry = 'UPDATE campus.tbl_beispiel SET'.
			       ' uebung_id='.$this->db_add_param($this->uebung_id, FHC_INTEGER).','.
			       ' punkte='.$this->db_add_param($this->punkte).','.
			       ' bezeichnung='.$this->db_add_param($this->bezeichnung).','.
			       ' updateamum='.$this->db_add_param($this->updateamum).','.
				   ' nummer='.$this->db_add_param($this->nummer).','.
			       ' updatevon='.$this->db_add_param($this->updatevon).
			       " WHERE beispiel_id=".$this->db_add_param($this->beispiel_id).";";
		}

		if($this->db_query($qry))
		{
			if($new)
			{
				$qry = "SELECT currval('campus.tbl_beispiel_beispiel_id_seq') as id;";
				if($this->db_query($qry))
				{
					if($row = $this->db_fetch_object())
					{
						$this->uebung_id = $row->id;
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
				else
				{
					$this->errormsg = 'Fehler beim Auslesen der Sequence';
					$this->db_query('ROLLBACK');
					return false;
				}
			}
			else
				return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern des Beispiels';
			return false;
		}
	}

	/**
	 * Prueft ob bereits zu dieser Uebung bereits ein Beispiel 
	 * mit dieser Bezeichnung vorhanden ist
	 *
	 * @param $uebung_id
	 * @param $bezeichnung
	 * @return boolean
	 */
	public function exists($uebung_id, $bezeichnung)
	{
		if(!is_numeric($uebung_id))
		{
			$this->errormsg = 'Uebung_id muss eine gueltige Zahl sein';
			return false;
		}

		$qry = "SELECT beispiel_id FROM campus.tbl_beispiel 
				WHERE uebung_id=".$this->db_add_param($uebung_id, FHC_INTEGER)." AND bezeichnung=".$this->db_add_param($bezeichnung).';';

		if($this->db_query($qry))
		{
			if($this->db_num_rows()>0)
				return true;
			else
				return false;
		}
		else
		{
			$this->errormsg ='Fehler beim Lesen der Beispiele';
			return false;
		}
	}

	/**
	 * Prueft ob ein Beispiel existiert
	 *
	 * @param $uid
	 * @param $beispiel_id
	 * @return boolean
	 */
	public function studentbeispiel_exists($uid,$beispiel_id)
	{
		if(!is_numeric($beispiel_id))
		{
			$this->errormsg = 'Beispiel_id muss eine gueltige Zahl sein';
			return false;
		}

		$qry = "SELECT vorbereitet FROM campus.tbl_studentbeispiel 
				WHERE beispiel_id=".$this->db_add_param($beispiel_id,FHC_INTEGER)." AND student_uid=".$this->db_add_param($uid).';';

		if($this->db_query($qry))
		{
			if($this->db_num_rows()>0)
				return true;
			else
				return false;
		}
		else
		{
			$this->errormsg = 'Fehler beim Lesen der aus der DB';
			return false;
		}
	}

	/**
	 * Loescht ein Beispiel
	 *
	 * @param $beispiel_id
	 * @return unknown
	 */
	public function delete($beispiel_id)
	{
		if(!is_numeric($beispiel_id))
		{
			$this->errormsg = 'Beispiel_id muss eine gueltige Zahl sein';
			return false;
		}

		$qry = "DELETE FROM campus.tbl_studentbeispiel WHERE beispiel_id=".$this->db_add_param($beispiel_id, FHC_INTEGER).";
				DELETE FROM campus.tbl_beispiel WHERE beispiel_id=".$this->db_add_param($beispiel_id, FHC_INTEGER).";";

		if($this->db_query($qry))
			return true;
		else
		{
			$this->errormsg = 'Fehler beim Löschen des Beispiels';
			return false;
		}
	}

	/**
	 * Laedt ein Beispiel eines Studenten
	 *
	 * @param $uid
	 * @param $beispiel_id
	 * @return boolean
	 */
	public function load_studentbeispiel($uid, $beispiel_id)
	{
		if(!is_numeric($beispiel_id))
		{
			$this->errormsg = 'Beispiel_id muss eine gueltige Zahl sein';
			return false;
		}
		$qry = "SELECT * FROM campus.tbl_studentbeispiel 
				WHERE student_uid=".$this->db_add_param($uid)." AND beispiel_id=".$this->db_add_param($beispiel_id, FHC_INTEGER).';';

		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$this->beispiel_id = $row->beispiel_id;
				$this->student_uid = $row->student_uid;
				$this->vorbereitet = $this->db_parse_bool($row->vorbereitet);
				$this->probleme = $this->db_parse_bool($row->probleme);
				$this->updateamum = $row->updateamum;
				$this->updatevon = $row->updatevon;
				$this->insertamum = $row->insertamum;
				$this->insertvon = $row->insertvon;
				return true;
			}
			else
			{
				$this->errormsg = 'Fehler beim Laden des Student_Beispiels';
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden des Student_Beispiels';
			return false;
		}
	}

	/**
	 * Prueft die Anzahl der vorbereiteten Beispiele
	 *
	 * @param $beispiel_id
	 * @return boolean
	 */
	public function check_anzahl_studentbeispiel($beispiel_id)
	{
		if(!is_numeric($beispiel_id))
		{
			$this->errormsg = 'Beispiel_id muss eine gueltige Zahl sein';
			return false;
		}
		$qry = "SELECT count(*) as anzahl FROM campus.tbl_studentbeispiel 
				WHERE vorbereitet = true and beispiel_id=".$this->db_add_param($beispiel_id, FHC_INTEGER).';';

		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$this->anzahl_studentbeispiel = $row->anzahl;
				return true;
			}
			else
			{
				$this->errormsg = 'Fehler beim Laden der Anzahl der Eintraege in Student_Beispiel';
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Anzahl der Eintraege in Student_Beispiel';
			return false;
		}
	}
	
	/**
	 * Prueft die studentbeispiel Daten auf Gueltigkeit
	 */
	private function studentbeispiel_validate()
	{
		if(!is_numeric($this->beispiel_id))
		{
			$this->errormsg = 'Beispiel_id muss eine gueltige Zahl sein';
			return false;
		}
		return true;
	}

	/**
	 * Speichert einen Studentbeispiel Datensatz in die DB
	 *
	 */
	public function studentbeispiel_save($new=null)
	{
		if(is_null($new))
			$new = $this->new;

		//Variablen auf Gueltigkeit pruefen
		if(!$this->studentbeispiel_validate())
			return false;

		if($new)
		{
			$qry = 'INSERT INTO campus.tbl_studentbeispiel(student_uid, beispiel_id, vorbereitet, probleme,
					updateamum, updatevon, insertamum, insertvon) VALUES('.
			        $this->db_add_param($this->student_uid).','.
			        $this->db_add_param($this->beispiel_id, FHC_INTEGER).','.
			        $this->db_add_param($this->vorbereitet,FHC_BOOLEAN).','.
			        $this->db_add_param($this->probleme).','.
			        $this->db_add_param($this->updateamum).','.
			        $this->db_add_param($this->updatevon).','.
			        $this->db_add_param($this->insertamum).','.
					$this->db_add_param($this->insertvon).');';
		}
		else
		{
			$qry = 'UPDATE campus.tbl_studentbeispiel SET'.
			       ' vorbereitet='.$this->db_add_param($this->vorbereitet, FHC_BOOLEAN).','.
			       ' probleme='.$this->db_add_param($this->probleme, FHC_BOOLEAN).','.
			       ' updateamum='.$this->db_add_param($this->updateamum).','.
			       ' updatevon='.$this->db_add_param($this->updatevon).
			       " WHERE beispiel_id=".$this->db_add_param($this->beispiel_id, FHC_INTEGER)." AND student_uid=".$this->db_add_param($this->student_uid).';';
		}

		if($this->db_query($qry))
		{
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern des Beispiels';
			return false;
		}
	}
}
?>