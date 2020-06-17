<?php
/* Copyright (C) 2006 FH Technikum-Wien
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
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>,
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at> and
 *          Andreas Moik <moik@technikum-wien.at>.
 */
/**
 * Klasse zur Verwaltung der Abschlusspruefungen
 */
require_once(dirname(__FILE__).'/basis_db.class.php');

class abschlusspruefung extends basis_db
{
	public $new;
	public $result = array();

	//Tabellenspalten
	public $abschlusspruefung_id;
	public $student_uid;
	public $vorsitz;
	public $pruefer1;
	public $pruefer2;
	public $pruefer3;
	public $abschlussbeurteilung_kurzbz;
	public $note;
	public $akadgrad_id;
	public $datum;
	public $uhrzeit;
	public $sponsion;
	public $pruefungstyp_kurzbz;
	public $anmerkung;
	public $updateamum;
	public $updatevon;
	public $insertamum;
	public $insertvon;
	public $ext_id;
	public $protokoll;
	public $endezeit;
	public $pruefungsantritt_kurzbz;
	public $freigabedatum;


	/**
	 * Konstruktor
	 * @param abschlusspruefung_id ID des zu ladenden Datensatzes
	 */
	public function __construct($abschlusspruefung_id=null)
	{
		parent::__construct();

		if(!is_null($abschlusspruefung_id))
			$this->load($abschlusspruefung_id);
	}

	/**
	 * Laedt einen Datensatz
	 * @param abschlusspruefung_id ID des zu ladenden Datensatzes
	 */
	public function load($abschlusspruefung_id)
	{
		//id auf Gueltigkeit pruefen
		if(!is_numeric($abschlusspruefung_id))
		{
			$this->errormsg = 'abschlusspruefung_id muss eine gueltige Zahl sein';
			return false;
		}

		//laden des Datensatzes
		$qry = "SELECT
					*
				FROM
					lehre.tbl_abschlusspruefung
					JOIN lehre.tbl_pruefungstyp USING (pruefungstyp_kurzbz)
				WHERE abschlusspruefung_id=".$this->db_add_param($abschlusspruefung_id, FHC_INTEGER, false).";";

		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$this->abschlusspruefung_id = $row->abschlusspruefung_id;
				$this->student_uid = $row->student_uid;
				$this->vorsitz = $row->vorsitz;
				$this->pruefer1 = $row->pruefer1;
				$this->pruefer2 = $row->pruefer2;
				$this->pruefer3 = $row->pruefer3;
				$this->abschlussbeurteilung_kurzbz = $row->abschlussbeurteilung_kurzbz;
				$this->note = $row->note;
				$this->akadgrad_id = $row->akadgrad_id;
				$this->datum = $row->datum;
				$this->uhrzeit = $row->uhrzeit;
				$this->sponsion = $row->sponsion;
				$this->pruefungstyp_kurzbz = $row->pruefungstyp_kurzbz;
				$this->beschreibung = $row->beschreibung;
				$this->anmerkung = $row->anmerkung;
				$this->updateamum = $row->updateamum;
				$this->updatevon = $row->updatevon;
				$this->insertamum = $row->insertamum;
				$this->insertvon = $row->insertvon;
				$this->ext_id = $row->ext_id;
				$this->protokoll = $row->protokoll;
				$this->endezeit = $row->endezeit;
				$this->pruefungsantritt_kurzbz = $row->pruefungsantritt_kurzbz;
				$this->freigabedatum = $row->freigabedatum;
				return true;
			}
			else
			{
				$this->errormsg = 'Fehler bei der Datenbankabfrage';
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Fehler bei der Datenbankabfrage';
			return false;
		}
	}

	/**
	 * Loescht einen Datensatz
	 * @param abschlusspruefung_id ID des zu loeschenden Datensatzes
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function delete($abschlusspruefung_id)
	{
		//abschlusspruefung_id auf Gueltigkeit pruefen
		if(!is_numeric($abschlusspruefung_id))
		{
			$this->errormsg = 'abschlusspruefung_id muss eine gueltige Zahl sein';
			return false;
		}

		$qry = "DELETE FROM lehre.tbl_abschlusspruefung
				WHERE abschlusspruefung_id=".$this->db_add_param($abschlusspruefung_id, FHC_INTEGER, false).";";

		if($this->db_query($qry))
		{
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Loeschen';
			return false;
		}
	}

	/**
	 * Prueft die Daten vor dem Speichern
	 *
	 * @return true wenn ok, false wenn Fehler
	 */
	protected function validate()
	{
		if($this->akadgrad_id=='')
		{
			$this->errormsg = 'AkadGrad muss eingegeben werden';
			return false;
		}
		if($this->pruefungstyp_kurzbz=='')
		{
			$this->errormsg = 'Pruefungstyp muss eingetragen werden';
			return false;
		}
		if($this->student_uid=='')
		{
			$this->errormsg = 'UID muss eingetragen werden';
			return false;
		}
		return true;
	}

	/**
	 * Speichert den aktuellen Datensatz
	 * Wenn $neu auf true gesetzt ist wird ein neuer Datensatz angelegt
	 * andernfalls wird der Datensatz mit der ID in $akte_id aktualisiert
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function save($new=null)
	{
		if(!$this->validate())
			return false;
		if($new==null)
			$new = $this->new;

		if($new)
		{
			//Neuen Datensatz anlegen
			$qry = "BEGIN;INSERT INTO lehre.tbl_abschlusspruefung (student_uid, vorsitz, pruefer1,
					pruefer2, pruefer3, abschlussbeurteilung_kurzbz, akadgrad_id, datum, uhrzeit, sponsion,
					pruefungstyp_kurzbz, anmerkung, updateamum, updatevon, insertamum, insertvon,
					note, protokoll, endezeit, pruefungsantritt_kurzbz, freigabedatum) VALUES (".
						$this->db_add_param($this->student_uid).', '.
						$this->db_add_param($this->vorsitz).', '.
						$this->db_add_param($this->pruefer1).', '.
						$this->db_add_param($this->pruefer2).', '.
						$this->db_add_param($this->pruefer3).', '.
						$this->db_add_param($this->abschlussbeurteilung_kurzbz).', '.
						$this->db_add_param($this->akadgrad_id, FHC_INTEGER).', '.
						$this->db_add_param($this->datum).', '.
						$this->db_add_param($this->uhrzeit, FHC_STRING, false).', '.
						$this->db_add_param($this->sponsion).', '.
						$this->db_add_param($this->pruefungstyp_kurzbz).', '.
						$this->db_add_param($this->anmerkung).', '.
						$this->db_add_param($this->updateamum).', '.
						$this->db_add_param($this->updatevon).', '.
						$this->db_add_param($this->insertamum).', '.
						$this->db_add_param($this->insertvon).', '.
						$this->db_add_param($this->note, FHC_INTEGER).','.
						$this->db_add_param($this->protokoll).','.
						$this->db_add_param($this->endezeit).','.
						$this->db_add_param($this->pruefungsantritt_kurzbz).','.
						$this->db_add_param($this->freigabedatum).');';

		}
		else
		{
			//Bestehenden Datensatz aktualisieren
			$qry= "UPDATE lehre.tbl_abschlusspruefung SET".
				" student_uid=".$this->db_add_param($this->student_uid).",".
				" vorsitz=".$this->db_add_param($this->vorsitz).",".
				" pruefer1=".$this->db_add_param($this->pruefer1).",".
				" pruefer2=".$this->db_add_param($this->pruefer2).",".
				" pruefer3=".$this->db_add_param($this->pruefer3).",".
				" abschlussbeurteilung_kurzbz=".$this->db_add_param($this->abschlussbeurteilung_kurzbz).",".
				" note=".$this->db_add_param($this->note, FHC_INTEGER).",".
				" akadgrad_id=".$this->db_add_param($this->akadgrad_id, FHC_INTEGER).",".
				" datum=".$this->db_add_param($this->datum).",".
				" uhrzeit=".$this->db_add_param($this->uhrzeit, FHC_STRING, false).",".
				" sponsion=".$this->db_add_param($this->sponsion).",".
				" pruefungstyp_kurzbz=".$this->db_add_param($this->pruefungstyp_kurzbz).",".
				" anmerkung=".$this->db_add_param($this->anmerkung).",".
				" updateamum=".$this->db_add_param($this->updateamum).",".
				" updatevon=".$this->db_add_param($this->updatevon).",".
				" protokoll=".$this->db_add_param($this->protokoll).",".
				" endezeit=".$this->db_add_param($this->endezeit).",".
				" pruefungsantritt_kurzbz=".$this->db_add_param($this->pruefungsantritt_kurzbz).",".
				" freigabedatum=".$this->db_add_param($this->freigabedatum)." ".
				" WHERE abschlusspruefung_id=".$this->db_add_param($this->abschlusspruefung_id, FHC_INTEGER, false);
		}

		if($this->db_query($qry))
		{
			if($new)
			{
				$qry = "SELECT currval('lehre.tbl_abschlusspruefung_abschlusspruefung_id') as id";
				if($this->db_query($qry))
				{
					if($row = $this->db_fetch_object())
					{
						$this->abschlusspruefung_id = $row->id;
						$this->db_query('COMMIT;');
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
			$this->errormsg = 'Fehler beim Speichern des Datensatzes';
			return false;
		}
	}

	/**
	 * Laedt alle Abschlusspruefungen eines Studenten
	 * @param student_uid UID des Studenten
	 * @return true wenn ok, false wenn Fehler
	 */
	public function getAbschlusspruefungen($student_uid)
	{
		$qry = "SELECT
					*
				FROM
					lehre.tbl_abschlusspruefung
					JOIN lehre.tbl_pruefungstyp USING (pruefungstyp_kurzbz)
				WHERE student_uid=".$this->db_add_param($student_uid, FHC_STRING, false)."
				ORDER BY datum DESC";

		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$obj = new abschlusspruefung();

				$obj->abschlusspruefung_id = $row->abschlusspruefung_id;
				$obj->student_uid = $row->student_uid;
				$obj->vorsitz = $row->vorsitz;
				$obj->pruefer1 = $row->pruefer1;
				$obj->pruefer2 = $row->pruefer2;
				$obj->pruefer3 = $row->pruefer3;
				$obj->abschlussbeurteilung_kurzbz = $row->abschlussbeurteilung_kurzbz;
				$obj->note = $row->note;
				$obj->akadgrad_id = $row->akadgrad_id;
				$obj->datum = $row->datum;
				$obj->uhrzeit = $row->uhrzeit;
				$obj->sponsion = $row->sponsion;
				$obj->pruefungstyp_kurzbz = $row->pruefungstyp_kurzbz;
				$obj->beschreibung = $row->beschreibung;
				$obj->anmerkung = $row->anmerkung;
				$obj->updateamum = $row->updateamum;
				$obj->updatevon = $row->updatevon;
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;
				$obj->ext_id = $row->ext_id;
				$obj->protokoll = $row->protokoll;
				$obj->endezeit = $row->endezeit;
				$obj->pruefungsantritt_kurzbz = $row->pruefungsantritt_kurzbz;
				$obj->freigabedatum = $row->freigabedatum;

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


	/**
	 * Liefert die letzte Abschlussprüfung eines Studenten
	 * @param type $student_uid
	 */
	public function getLastAbschlusspruefung($student_uid)
	{
		$qry = "SELECT
					*
				FROM
					lehre.tbl_abschlusspruefung
					JOIN lehre.tbl_pruefungstyp USING (pruefungstyp_kurzbz)
				WHERE student_uid=".$this->db_add_param($student_uid, FHC_STRING, false)."
				ORDER BY datum DESC LIMIT 1";

		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$this->abschlusspruefung_id = $row->abschlusspruefung_id;
				$this->student_uid = $row->student_uid;
				$this->vorsitz = $row->vorsitz;
				$this->pruefer1 = $row->pruefer1;
				$this->pruefer2 = $row->pruefer2;
				$this->pruefer3 = $row->pruefer3;
				$this->abschlussbeurteilung_kurzbz = $row->abschlussbeurteilung_kurzbz;
				$this->note = $row->note;
				$this->akadgrad_id = $row->akadgrad_id;
				$this->datum = $row->datum;
				$this->uhrzeit = $row->uhrzeit;
				$this->sponsion = $row->sponsion;
				$this->pruefungstyp_kurzbz = $row->pruefungstyp_kurzbz;
				$this->beschreibung = $row->beschreibung;
				$this->anmerkung = $row->anmerkung;
				$this->updateamum = $row->updateamum;
				$this->updatevon = $row->updatevon;
				$this->insertamum = $row->insertamum;
				$this->insertvon = $row->insertvon;
				$this->ext_id = $row->ext_id;
				$this->protokoll = $row->protokoll;
				$this->endezeit = $row->endezeit;
				$this->pruefungsantritt_kurzbz = $row->pruefungsantritt_kurzbz;
				$this->freigabedatum = $row->freigabedatum;

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
