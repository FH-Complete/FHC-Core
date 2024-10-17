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
 * Verwaltet die Uebungen des Kreuzerltools
 */
require_once(dirname(__FILE__).'/basis_db.class.php');

class uebung extends basis_db
{
	public $new;      // boolean
	public $uebungen = array(); // lehreinheit Objekt

	//Tabellenspalten
	public $uebung_id;		// serial
	public $gewicht;		// smalint
	public $punkte;			// Real
	public $angabedatei;	// oid
	public $freigabevon;	// timestamp
	public $freigabebis;	// timestamp
	public $abgabe;			// boolean
	public $beispiele;		// boolean
	public $bezeichnung;	// varchar(32)
	public $positiv;		// boolean
	public $defaultbemerkung;	// text
	public $lehreinheit_id;	// integer
	public $updateamum;		// timestamp
	public $updatevon;		// varchar(16)
	public $insertamum;		// timestamp
	public $insertvon;		// varchar(16)
	public $statistik;		// boolean
	public $liste_id;		//integer
	public $maxbsp;			//smallint
	public $maxstd;			//smallint
	public $nummer;			//smallint
	public $prozent;

	//Studentuebung
	public $student_uid;		// varchar(16)
	public $mitarbeiter_uid;	// varchar(16)
	public $abgabe_id;			// integer
	public $note;				// smalint
	public $mitarbeitspunkte;	// smalint
	public $anmerkung;			// text
	public $benotungsdatum;		// timestamp
	
	//Abgabe
	public $abgabe_abgabe_id;	// integer
	public $abgabedatei;		// varchar(64)
	public $abgabezeit;			// timestamp
	public $abgabe_anmerkung;	// text

	/**
	 * Konstruktor - laedt optional eine Uebung
	 * @param $uebung_id
	 */
	public function __construct($uebung_id=null)
	{
		parent::__construct();
		
		if($uebung_id!=null)
			$this->load($uebung_id);
	}

	/**
	 * Laedt die Uebung
	 * @param uebung_id
	 */
	public function load($uebung_id)
	{
		if(!is_numeric($uebung_id))
		{
			$this->errormsg='Uebung_id muss eine gueltige Zahl sein';
			return false;
		}
		$qry = "SELECT * FROM campus.tbl_uebung WHERE uebung_id=".$this->db_add_param($uebung_id, FHC_INTEGER).';';

		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$this->uebung_id = $row->uebung_id;
				$this->gewicht = $row->gewicht;
				$this->punkte = $row->punkte;
				$this->angabedatei = $row->angabedatei;
				$this->freigabevon = $row->freigabevon;
				$this->freigabebis = $row->freigabebis;
				$this->abgabe = $this->db_parse_bool($row->abgabe);
				$this->beispiele = $this->db_parse_bool($row->beispiele);
				$this->bezeichnung = $row->bezeichnung;
				$this->positiv = $this->db_parse_bool($row->positiv);
				$this->defaultbemerkung = $row->defaultbemerkung;
				$this->lehreinheit_id = $row->lehreinheit_id;
				$this->updateamum = $row->updateamum;
				$this->updatevon = $row->updatevon;
				$this->insertamum = $row->insertamum;
				$this->insertvon = $row->insertvon;
				$this->statistik = $this->db_parse_bool($row->statistik);
				$this->liste_id = $row->liste_id;
				$this->maxbsp = $row->maxbsp;
				$this->maxstd = $row->maxstd;
				$this->nummer = $row->nummer;
				$this->prozent = $row->prozent;
				return true;
			}
			else
			{
				$this->errormsg = "Es ist keine Uebung mit dieser ID vorhanden";
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Uebung';
			return false;
		}
	}

	/**
	 * Aendert den Status des Boolean Prozent
	 *
	 * @param $uebung_id
	 * @return true wenn geaendert, sonst false
	 */
	public function toggle_prozent_punkte($uebung_id)
	{
		$qry = "UPDATE campus.tbl_uebung SET prozent = not prozent WHERE uebung_id = ".$this->db_add_param($uebung_id, FHC_INTEGER).";";
		if($this->db_query($qry))
			return true;
		else
		{
			$this->errormsg = "toggle misslungen";
			return false;
		}		
	}	
	
	/**
	 * Laedt eine Studentuebung Zuordnung
	 *
	 * @param $student_uid
	 * @param $uebung_id
	 * @return boolean
	 */
	public function load_studentuebung($student_uid, $uebung_id)
	{
		$qry = "SELECT * FROM campus.tbl_studentuebung WHERE student_uid=".$this->db_add_param($student_uid)." AND uebung_id=".$this->db_add_param($uebung_id, FHC_INTEGER).";";

		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$this->student_uid = $row->student_uid;
				$this->mitarbeiter_uid = $row->mitarbeiter_uid;
				$this->abgabe_id = $row->abgabe_id;
				$this->uebung_id = $row->uebung_id;
				$this->note = $row->note;
				$this->mitarbeitspunkte = $row->mitarbeitspunkte;
				$this->punkte = $row->punkte;
				$this->anmerkung = $row->anmerkung;
				$this->benotungsdatum = $row->benotungsdatum;
				$this->updateamum = $row->updateamum;
				$this->updatevon = $row->updatevon;
				$this->insertamum = $row->insertamum;
				$this->insertvon = $row->insertvon;
				return true;
			}
			else
			{
				$this->errormsg = 'Es gibt keinen passenden Eintrag';
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden des eintrages';
			return false;
		}
	}

	/**
	 * Laedt eine Abgabe
	 *
	 * @param $abgabe_id
	 * @return boolean
	 */
	public function load_abgabe($abgabe_id)
	{
		$qry = "SELECT * FROM campus.tbl_abgabe WHERE abgabe_id = ".$this->db_add_param($abgabe_id, FHC_INTEGER).";";

		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$this->abgabe_id = $row->abgabe_id;
				$this->abgabedatei = $row->abgabedatei;
				$this->abgabezeit = $row->abgabezeit;
				$this->abgabe_anmerkung = $row->anmerkung;
				return true;
			}
			else
			{
				$this->errormsg = 'Es gibt keinen passenden Eintrag';
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden des Eintrages';
			return false;
		}
	}
	
	/**
	 * Prueft ob zu einer Uebung bereits Studenten zugeordnet sind
	 *
	 * @param $uebung_id
	 * @return true wenn ja, sonst false
	 */
	public function check_studentuebung($uebung_id)
	{
		$qry = "SELECT * FROM campus.tbl_studentuebung WHERE uebung_id=".$this->db_add_param($uebung_id, FHC_INTEGER).";";

		if($this->db_query($qry))	
		{
			if($this->db_num_rows() >0)			
				return true;
			else
				return false;
		}
		else
			return false;	
	}

	/**
	 * Liefert die naechste Nummer einer uebung
	 * @return boolean, naechste nummer steht in this->next_nummer
	 */
	public function get_next_nummer()
	{
		$qry = "SELECT max(nummer) FROM campus.tbl_uebung";
		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$next = $row->max + 1;
				$this->next_nummer = $next;
				return true;
			}
			else 
			{
				$this->errormsg = 'Fehler beim Ermitteln der naechsten Nummer';
				return false;
			}
		}
		else 
		{
			$this->errormsg = 'Fehler bei einer Abfrage';
			return false;
		}
	}
	
	/**
	 * Laedt eine Uebung
	 *
	 * @param $lehreinheit_id
	 * @param $level
	 * @param $uebung_id
	 * @return boolean
	 */
	public function load_uebung($lehreinheit_id, $level=null, $uebung_id=null)
	{
		if(!is_numeric($lehreinheit_id))
		{
			$this->errormsg = 'Lehreinheit_id muss eine gueltige Zahl sein';
			return false;
		}

		$qry = "SELECT * FROM campus.tbl_uebung WHERE lehreinheit_id=".$this->db_add_param($lehreinheit_id, FHC_INTEGER);
		if ($level == 1)
			$qry .= " and liste_id is null";
		if ($level == 2)
			$qry .= " and liste_id = ".$this->db_add_param($uebung_id, FHC_INTEGER);
		$qry .= " ORDER BY bezeichnung";

		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$uebung_obj = new uebung();
				
				$uebung_obj->uebung_id = $row->uebung_id;
				$uebung_obj->gewicht = $row->gewicht;
				$uebung_obj->punkte = $row->punkte;
				$uebung_obj->angabedatei = $row->angabedatei;
				$uebung_obj->freigabevon = $row->freigabevon;
				$uebung_obj->freigabebis = $row->freigabebis;
				$uebung_obj->abgabe = $this->db_parse_bool($row->abgabe);
				$uebung_obj->beispiele = $this->db_parse_bool($row->beispiele);
				$uebung_obj->bezeichnung = $row->bezeichnung;
				$uebung_obj->positiv = $this->db_parse_bool($row->positiv);
				$uebung_obj->defaultbemerkung = $row->defaultbemerkung;
				$uebung_obj->lehreinheit_id = $row->lehreinheit_id;
				$uebung_obj->updateamum = $row->updateamum;
				$uebung_obj->updatevon = $row->updatevon;
				$uebung_obj->insertamum = $row->insertamum;
				$uebung_obj->insertvon = $row->insertvon;
				$uebung_obj->statistik = $this->db_parse_bool($row->statistik);
				$uebung_obj->liste_id = $row->liste_id;
				$uebung_obj->maxstd = $row->maxstd;
				$uebung_obj->maxbsp = $row->maxbsp;
				$uebung_obj->nummer = $row->nummer;
				$uebung_obj->prozent = $row->prozent;

				$this->uebungen[] = $uebung_obj;
			}
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Uebung';
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
		if(!is_numeric($this->lehreinheit_id))
		{
			$this->errormsg = 'Lehreinheit_id muss eine gueltige Zahl sein';
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
	 * Speichert Uebung in die Datenbank
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
			$qry = 'BEGIN; INSERT INTO campus.tbl_uebung(gewicht, punkte, angabedatei, freigabevon, freigabebis,
			        abgabe, beispiele, bezeichnung, positiv, defaultbemerkung, lehreinheit_id, updateamum,
			        updatevon, insertamum, insertvon, liste_id, maxstd, maxbsp, nummer, statistik) VALUES('.
			        $this->db_add_param($this->gewicht).','.
			        $this->db_add_param($this->punkte).','.
			        $this->db_add_param($this->angabedatei).','.
			        $this->db_add_param($this->freigabevon).','.
			        $this->db_add_param($this->freigabebis).','.
			        $this->db_add_param($this->abgabe, FHC_BOOLEAN).','.
			        $this->db_add_param($this->beispiele, FHC_BOOLEAN).','.
			        $this->db_add_param($this->bezeichnung).','.
			        $this->db_add_param($this->positiv, FHC_BOOLEAN).','.
			        $this->db_add_param($this->defaultbemerkung).','.
			        $this->db_add_param($this->lehreinheit_id).','.
			        $this->db_add_param($this->updateamum).','.
			        $this->db_add_param($this->updatevon).','.
			        $this->db_add_param($this->insertamum).','.
			        $this->db_add_param($this->insertvon).','.
					$this->db_add_param($this->liste_id).','.
					$this->db_add_param($this->maxstd).','.
					$this->db_add_param($this->maxbsp).','.
					$this->db_add_param($this->nummer).','.
			        $this->db_add_param($this->statistik, FHC_BOOLEAN).');';
		}
		else
		{
			$qry = 'UPDATE campus.tbl_uebung SET'.
			       ' gewicht='.$this->db_add_param($this->gewicht).','.
			       ' punkte='.$this->db_add_param($this->punkte).','.
			       ' angabedatei='.$this->db_add_param($this->angabedatei).','.
			       ' freigabevon='.$this->db_add_param($this->freigabevon).','.
			       ' freigabebis='.$this->db_add_param($this->freigabebis).','.
			       ' abgabe='.$this->db_add_param($this->abgabe, FHC_BOOLEAN).','.
			       ' beispiele='.$this->db_add_param($this->beispiele, FHC_BOOLEAN).','.
			       ' bezeichnung='.$this->db_add_param($this->bezeichnung).','.
			       ' positiv='.$this->db_add_param($this->positiv, FHC_BOOLEAN).','.
			       ' defaultbemerkung='.$this->db_add_param($this->defaultbemerkung).','.
			       ' lehreinheit_id='.$this->db_add_param($this->lehreinheit_id, FHC_INTEGER).','.
			       ' updateamum='.$this->db_add_param($this->updateamum).','.
			       ' updatevon='.$this->db_add_param($this->updatevon).','.
				   ' liste_id='.$this->db_add_param($this->liste_id).','.
				   ' maxstd='.$this->db_add_param($this->maxstd).','.
				   ' maxbsp='.$this->db_add_param($this->maxbsp).','.
				   ' nummer='.$this->db_add_param($this->nummer).','.
			       ' statistik='.$this->db_add_param($this->statistik, FHC_BOOLEAN).
			       " WHERE uebung_id=".$this->db_add_param($this->uebung_id, FHC_INTEGER, false).";";
		}

		if($this->db_query($qry))
		{
			if($new)
			{
				$qry = "SELECT currval('campus.tbl_uebung_uebung_id_seq') as id;";
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
			$this->errormsg = 'Fehler beim Speichern der Uebung';
			return false;
		}
	}

	/**
	 * Prueft die Variablen vor dem Speichern
	 * auf Gueltigkeit.
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function validate_studentuebung()
	{
		if(!is_numeric($this->uebung_id))
		{
			$this->errormsg = 'Uebung_id muss eine gueltige Zahl sein';
			return false;
		}
		if($this->student_uid=='')
		{
			$this->errormsg = 'Student_uid muss eingetragen werden';
			return false;
		}
		return true;
	}

	/**
	 * Speichert StudentUebung in die Datenbank
	 * Wenn $new auf true gesetzt ist wird ein neuer Datensatz
	 * angelegt, ansonsten der Datensatz upgedated
	 * @return true wenn erfolgreich, false im Fehlerfall
	 */
	public function studentuebung_save($new=null)
	{
		if(is_null($new))
			$new = $this->new;

		//Variablen auf Gueltigkeit pruefen
		if(!$this->validate_studentuebung())
			return false;

		if($new)
		{
			$qry = 'INSERT INTO campus.tbl_studentuebung(student_uid, mitarbeiter_uid, abgabe_id, uebung_id,
					note, mitarbeitspunkte, punkte, anmerkung, benotungsdatum, updateamum,
			        updatevon, insertamum, insertvon) VALUES('.
			        $this->db_add_param($this->student_uid).','.
			        $this->db_add_param($this->mitarbeiter_uid).','.
			        $this->db_add_param($this->abgabe_id, FHC_INTEGER).','.
			        $this->db_add_param($this->uebung_id, FHC_INTEGER).','.
			        $this->db_add_param($this->note).','.
			        $this->db_add_param($this->mitarbeitspunkte).','.
			        $this->db_add_param($this->punkte).','.
			        $this->db_add_param($this->anmerkung).','.
			        $this->db_add_param($this->benotungsdatum).','.
			        $this->db_add_param($this->updateamum).','.
			        $this->db_add_param($this->updatevon).','.
			        $this->db_add_param($this->insertamum).','.
			        $this->db_add_param($this->insertvon).');';
		}
		else
		{
			$qry = 'UPDATE campus.tbl_studentuebung SET'.
			       ' mitarbeiter_uid='.$this->db_add_param($this->mitarbeiter_uid).','.
			       ' abgabe_id='.$this->db_add_param($this->abgabe_id, FHC_INTEGER).','.
			       ' uebung_id='.$this->db_add_param($this->uebung_id, FHC_INTEGER).','.
			       ' note='.$this->db_add_param($this->note).','.
			       ' mitarbeitspunkte='.$this->db_add_param($this->mitarbeitspunkte).','.
			       ' punkte='.$this->db_add_param($this->punkte).','.
			       ' anmerkung='.$this->db_add_param($this->anmerkung).','.
			       ' benotungsdatum='.$this->db_add_param($this->benotungsdatum).','.
			       ' updateamum='.$this->db_add_param($this->updateamum).','.
			       ' updatevon='.$this->db_add_param($this->updatevon).
			       " WHERE uebung_id=".$this->db_add_param($this->uebung_id, FHC_INTEGER)." AND student_uid=".$this->db_add_param($this->student_uid).";";
		}

		if($this->db_query($qry))
		{
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern der StudentUebung';
			return false;
		}
	}

	/**
	 * Speichert eine Abgabe
	 *
	 * @param $new
	 * @return boolean
	 */
	public function abgabe_save($new=null)
	{
		if(is_null($new))
			$new = $this->new;


		if($new)
		{
			$qry = 'INSERT INTO campus.tbl_abgabe(abgabedatei, abgabezeit, anmerkung) VALUES('.
			        $this->db_add_param($this->abgabedatei).','.
			        $this->db_add_param($this->abgabezeit).','.
			        $this->db_add_param($this->abgabe_anmerkung).');';
		}
		else
		{
			$qry = 'UPDATE campus.tbl_abgabe SET'.
			       ' abgabedatei='.$this->db_add_param($this->abgabedatei).','.
			       ' abgabezeit='.$this->db_add_param($this->abgabezeit).','.
			       ' anmerkung='.$this->db_add_param($this->abgabe_anmerkung).
			       " WHERE abgabe_id=".$this->db_add_param($this->abgabe_id, FHC_INTEGER).";";
		}

		if($this->db_query($qry))
		{
			if($new)
			{
				$qry = "SELECT currval('campus.tbl_abgabe_abgabe_id_seq') as id;";
				if($this->db_query($qry))
				{
					if($row = $this->db_fetch_object())
					{
						$this->abgabe_id = $row->id;
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
			$this->errormsg = 'Fehler beim Speichern der StudentUebung';
			return false;
		}
	}

	/**
	 * Loescht eine Uebung plus die abhaengigen eintraege in den
	 * Tabellen studentuebung, studentbeispiel, und beispiel
	 */
	public function delete($uebung_id)
	{
		if(!is_numeric($uebung_id))
		{
			$this->errormsg = 'Uebung_id ist ungueltig';
			return false;
		}
		
		// subuebungen wegraeumen
		$qry = "SELECT uebung_id, angabedatei FROM campus.tbl_uebung WHERE liste_id=".$this->db_add_param($uebung_id, FHC_INTEGER, false).';';
		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{			
				$angabe = BENOTUNGSTOOL_PATH."angabe/".$row->angabedatei;
				if(file_exists($angabe))
					unlink($angabe);

				$qry = "DELETE FROM campus.tbl_studentbeispiel WHERE beispiel_id IN(SELECT beispiel_id FROM campus.tbl_beispiel WHERE uebung_id=".$this->db_add_param($row->uebung_id, FHC_INTEGER).");
						DELETE FROM campus.tbl_abgabe WHERE abgabe_id IN(SELECT abgabe_id FROM campus.tbl_studentuebung WHERE uebung_id=".$this->db_add_param($row->uebung_id, FHC_INTEGER).");
						DELETE FROM campus.tbl_studentuebung WHERE uebung_id=".$this->db_add_param($row->uebung_id, FHC_INTEGER).";
						DELETE FROM campus.tbl_beispiel WHERE uebung_id=".$this->db_add_param($row->uebung_id, FHC_INTEGER).";
						DELETE FROM campus.tbl_studentuebung WHERE uebung_id = ".$this->db_add_param($row->uebung_id, FHC_INTEGER).";
						DELETE FROM campus.tbl_uebung WHERE uebung_id=".$this->db_add_param($row->uebung_id, FHC_INTEGER).";";
			
				if(!$this->db_query($qry))
				{
					$this->errormsg = 'Fehler beim Loeschen der Daten';
					return false;
				}								
			}
		}
		$qry = "SELECT angabedatei FROM campus.tbl_uebung WHERE uebung_id=".$this->db_add_param($uebung_id, FHC_INTEGER).";";
		if($result = $this->db_query($qry))
		{
			if($row = $this->db_fetch_object($result))
			{
				if($row->angabedatei!='')
				{
					$angabe = BENOTUNGSTOOL_PATH."angabe/".$row->angabedatei;
					if(file_exists($angabe))
						unlink($angabe);
				}
			}
		}

		$qry = "DELETE FROM campus.tbl_studentbeispiel WHERE beispiel_id IN(SELECT beispiel_id FROM campus.tbl_beispiel WHERE uebung_id=".$this->db_add_param($uebung_id, FHC_INTEGER).");
				DELETE FROM campus.tbl_abgabe WHERE abgabe_id IN(SELECT abgabe_id FROM campus.tbl_studentuebung WHERE uebung_id=".$this->db_add_param($uebung_id, FHC_INTEGER).");
				DELETE FROM campus.tbl_studentuebung WHERE uebung_id=".$this->db_add_param($uebung_id, FHC_INTEGER).";
				DELETE FROM campus.tbl_beispiel WHERE uebung_id=".$this->db_add_param($uebung_id, FHC_INTEGER).";
				DELETE FROM campus.tbl_studentuebung WHERE uebung_id = ".$this->db_add_param($uebung_id, FHC_INTEGER).";
				DELETE FROM campus.tbl_uebung WHERE uebung_id=".$this->db_add_param($uebung_id, FHC_INTEGER).";";

		if($this->db_query($qry))
			return true;
		else
		{
			$this->errormsg = 'Fehler beim Loeschen der Daten';
			return false;
		}		
	}

	/**
	 * Loescht eine Uebung plus die abhaengigen eintraege in den
	 * Tabellen studentuebung, studentbeispiel, und beispiel
	 */
	public function delete_abgabe($abgabe_id)
	{
		if(!is_numeric($abgabe_id))
		{
			$this->errormsg = 'abgabe_id ist ungueltig';
			return false;
		}
		
		// subuebungen wegraeumen
		$qry = "SELECT * FROM campus.tbl_abgabe WHERE abgabe_id = ".$this->db_add_param($abgabe_id, FHC_INTEGER).";";
		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				if(file_exists(BENOTUNGSTOOL_PATH."abgabe/".$row->abgabedatei))
					unlink(BENOTUNGSTOOL_PATH."abgabe/".$row->abgabedatei);

				$qry = "UPDATE campus.tbl_studentuebung set abgabe_id = null where abgabe_id = ".$this->db_add_param($abgabe_id, FHC_INTEGER).";
						DELETE FROM campus.tbl_abgabe WHERE abgabe_id = ".$this->db_add_param($abgabe_id, FHC_INTEGER).";";
			
				if(!$this->db_query($qry))
				{
					$this->errormsg = 'Fehler beim Loeschen der Daten';
					return false;
				}
				else
					return true;							
			}
			else 
			{
				$this->errormsg='Keine Abgabe mit dieser ID vorhanden';
				return false;
			}
		}
		else 
		{
			$this->errormsg = 'Fehler bei einer Abfrage';
			return false;
		}	
	}
}
?>
