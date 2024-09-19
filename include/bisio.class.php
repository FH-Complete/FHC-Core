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
 * Klasse bisio - Incomming/Outgoing
 * @create 2007-05-14
 */
require_once(dirname(__FILE__).'/basis_db.class.php');

class bisio extends basis_db
{
	public $new;       					// boolean
	public $result = array();			// bisio Objekt

	//Tabellenspalten
	public $bisio_id; 					// serial
	public $mobilitaetsprogramm_code; 	// integer
	public $mobilitaetsprogramm_kurzbz; // varchar(16)
	public $nation_code; 				// varchar(3)
	public $von; 						// date
	public $bis; 						// date
	public $zweck_code; 				// varchar(20)
	public $student_uid; 				// varchar(16)
	public $updateamum; 				// timestamp
	public $updatevon; 					// varchar(32)
	public $insertamum; 				// timestamp
	public $insertvon; 					// varchar(32)
	public $ext_id;						// bigint
	public $ort;						// varchar(128)
	public $universitaet;				// varchar(256)
	public $lehreinheit_id;				// integer
	public $ects_erworben;				// numeric(5,2)
	public $ects_angerechnet;			// numeric(5,2)
	public $herkunftsland_code;			// varchar(3)

	public $aufenthaltfoerderung_code;	// integer
	public $bezeichnung;				// varchar(64)

	/**
	 * Konstruktor
	 * @param $bisio_id  ID die geladen werden soll (Default=null)
	 */
	public function __construct($bisio_id=null)
	{
		parent::__construct();

		if (!is_null($bisio_id))
			$this->load($bisio_id);
	}

	/**
	 * Laedt die Funktion mit der ID $buchungsnr
	 * @param  $buchungsnr ID der zu ladenden  Email
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function load($bisio_id)
	{
		if (!is_numeric($bisio_id))
		{
			$this->errormsg = 'ID muss eine gueltige Zahl sein';
			return false;
		}

		$qry = "SELECT * FROM bis.tbl_bisio WHERE bisio_id=".$this->db_add_param($bisio_id, FHC_INTEGER).";";

		if ($this->db_query($qry))
		{
			if ($row = $this->db_fetch_object())
			{
				$this->bisio_id = $row->bisio_id;
				$this->mobilitaetsprogramm_code = $row->mobilitaetsprogramm_code;
				$this->nation_code = $row->nation_code;
				$this->von = $row->von;
				$this->bis = $row->bis;
				$this->student_uid = $row->student_uid;
				$this->updateamum = $row->updateamum;
				$this->updatevon = $row->updatevon;
				$this->insertamum = $row->insertamum;
				$this->insertvon = $row->insertvon;
				$this->ext_id = $row->ext_id;
				$this->ort = $row->ort;
				$this->universitaet = $row->universitaet;
				$this->lehreinheit_id = $row->lehreinheit_id;
				$this->ects_angerechnet = $row->ects_angerechnet;
				$this->ects_erworben = $row->ects_erworben;
				$this->herkunftsland_code = $row->herkunftsland_code;

				return true;
			}
			else
			{
				$this->errormsg = 'Datensatz wurde nicht gefunden';
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
	 * Prueft die Variablen auf Gueltigkeit
	 * @return true wenn ok, false im Fehlerfall
	 */
	protected function validate()
	{
		if (!is_numeric($this->mobilitaetsprogramm_code))
		{
			$this->errormsg = 'Mobilitaetsprogramm ist ungueltig';
			return false;
		}

		if (mb_strlen($this->nation_code) > 3)
		{
			$this->errormsg = 'Nation ist ungueltig';
			return false;
		}

		if (mb_strlen($this->zweck_code) > 20)
		{
			$this->errormsg = 'Zweck ist ungueltig';
			return false;
		}

		if (mb_strlen($this->student_uid) > 32)
		{
			$this->errormsg = 'Student_UID ist ungueltig';
			return false;
		}

		if ($this->von != '' && !mb_ereg("([0-9]{4})-([0-9]{2})-([0-9]{2})", $this->von))
		{
			$this->errormsg = 'VON-Datum hat ein ungueltiges Format';
			return false;
		}

		if ($this->bis != '' && !mb_ereg("([0-9]{4})-([0-9]{2})-([0-9]{2})", $this->bis))
		{
			$this->errormsg = 'BIS-Datum hat ein ungueltiges Format';
			return false;
		}

		if ($this->ects_erworben != '' && !is_numeric($this->ects_erworben))
		{
			$this->errormsg = 'Erworbene ECTS sind ungültig';
			return false;
		}
		if ($this->ects_angerechnet != '' && !is_numeric($this->ects_angerechnet))
		{
			$this->errormsg = 'Angerechnete ECTS sind ungültig';
			return false;
		}
		if ($this->ects_erworben != ''
		 && $this->ects_angerechnet != ''
		 && $this->ects_angerechnet > $this->ects_erworben
		)
		{
			$this->errormsg = 'Angerechnete ECTS darf nicht groesser als erworbene ECTS sein.';
			return false;
		}

		return true;
	}

	/**
	 * Speichert den aktuellen Datensatz in die Datenbank
	 * Wenn $neu auf true gesetzt ist wird ein neuer Datensatz angelegt
	 * andernfalls wird der Datensatz mit der ID in $kontakt_id aktualisiert
	 * @param $new true wenn insert false wenn update
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function save($new=null)
	{
		//Variablen pruefen
		if (!$this->validate())
			return false;

		if ($new == null)
			$new = $this->new;

		if ($new)
		{
			//Neuen Datensatz einfuegen

			$qry='BEGIN;INSERT INTO bis.tbl_bisio (mobilitaetsprogramm_code, nation_code, von, bis,
				student_uid, updateamum, updatevon, insertamum, insertvon, ort, universitaet, lehreinheit_id,
				ects_angerechnet, ects_erworben, herkunftsland_code) VALUES('.
			     $this->db_add_param($this->mobilitaetsprogramm_code, FHC_INTEGER).', '.
			     $this->db_add_param($this->nation_code).', '.
			     $this->db_add_param($this->von).', '.
			     $this->db_add_param($this->bis).', '.
			     $this->db_add_param($this->student_uid).', '.
			     $this->db_add_param($this->updateamum).', '.
			     $this->db_add_param($this->updatevon).', '.
			     $this->db_add_param($this->insertamum).', '.
			     $this->db_add_param($this->insertvon).', '.
			     $this->db_add_param($this->ort).', '.
			     $this->db_add_param($this->universitaet).', '.
			     $this->db_add_param($this->lehreinheit_id, FHC_INTEGER).','.
			     $this->db_add_param($this->ects_angerechnet).', '.
			     $this->db_add_param($this->ects_erworben).', '.
			     $this->db_add_param($this->herkunftsland_code).');';
		}
		else
		{
			//Updaten des bestehenden Datensatzes
			$qry = 'UPDATE bis.tbl_bisio SET '.
				   ' mobilitaetsprogramm_code='.$this->db_add_param($this->mobilitaetsprogramm_code, FHC_INTEGER).','.
				   ' nation_code='.$this->db_add_param($this->nation_code).','.
				   ' von='.$this->db_add_param($this->von).','.
				   ' bis='.$this->db_add_param($this->bis).','.
				   ' student_uid='.$this->db_add_param($this->student_uid).','.
				   ' updateamum='.$this->db_add_param($this->updateamum).','.
				   ' updatevon='.$this->db_add_param($this->updatevon).','.
				   ' ort='.$this->db_add_param($this->ort).','.
				   ' universitaet='.$this->db_add_param($this->universitaet).','.
				   ' lehreinheit_id='.$this->db_add_param($this->lehreinheit_id, FHC_INTEGER).', '.
				   ' ects_angerechnet='.$this->db_add_param($this->ects_angerechnet).', '.
				   ' ects_erworben='.$this->db_add_param($this->ects_erworben).', '.
				   ' herkunftsland_code='.$this->db_add_param($this->herkunftsland_code).
				   " WHERE bisio_id=".$this->db_add_param($this->bisio_id, FHC_INTEGER).";";
		}

		if ($this->db_query($qry))
		{
			if ($new)
			{
				$qry = "SELECT currval('bis.tbl_bisio_bisio_id_seq') as id";
				if ($this->db_query($qry))
				{
					if ($row = $this->db_fetch_object())
					{
						$this->bisio_id = $row->id;
						$this->db_query('COMMIT;');
					}
					else
					{
						$this->errormsg = 'Fehler beim Auslesen der Sequence';
						$this->db_query('ROLLBACK;');
						return false;
					}
				}
				else
				{
					$this->errormsg = 'Fehler beim Auslesen der Sequence';
					$this->db_query('ROLLBACK;');
					return false;
				}
			}
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern der Daten';
			return true;
		}
	}

	/**
	 * Loescht den Datenensatz mit der ID die uebergeben wird
	 * @param bisio_id ID die geloescht werden soll
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function delete($bisio_id)
	{
		if (!is_numeric($bisio_id))
		{
			$this->errormsg = 'ID ist ungueltig';
			return false;
		}

		$qry = "DELETE FROM bis.tbl_bisio WHERE bisio_id=".$this->db_add_param($bisio_id, FHC_INTEGER).";";

		if ($this->db_query($qry))
			return true;
		else
		{
			$this->errormsg = 'Fehler beim Loeschen des Datensatzes';
			return false;
		}
	}

	/**
	 * Liefert alle Incomming/Outgoing
	 * Eintraege eines Studenten
	 * @param $uid
	 * @return true wenn ok, false wenn fehler
	 */
	public function getIO($uid)
	{
		$qry = "SELECT	tbl_bisio.*,
						tbl_mobilitaetsprogramm.kurzbz as mobilitaetsprogramm_kurzbz
				FROM
					bis.tbl_bisio,
					bis.tbl_mobilitaetsprogramm
				WHERE
					student_uid=".$this->db_add_param($uid)." AND
					tbl_mobilitaetsprogramm.mobilitaetsprogramm_code=tbl_bisio.mobilitaetsprogramm_code
				ORDER BY bis;";

		if ($this->db_query($qry))
		{
			while ($row = $this->db_fetch_object())
			{
				$io = new bisio();

				$io->bisio_id = $row->bisio_id;
				$io->mobilitaetsprogramm_code = $row->mobilitaetsprogramm_code;
				$io->mobilitaetsprogramm_kurzbz = $row->mobilitaetsprogramm_kurzbz;
				$io->nation_code = $row->nation_code;
				$io->von = $row->von;
				$io->bis = $row->bis;
				$io->student_uid = $row->student_uid;
				$io->updateamum = $row->updateamum;
				$io->updatevon = $row->updatevon;
				$io->insertamum = $row->insertamum;
				$io->insertvon = $row->insertvon;
				$io->ext_id = $row->ext_id;
				$io->ort = $row->ort;
				$io->universitaet = $row->universitaet;
				$io->lehreinheit_id = $row->lehreinheit_id;
				$io->ects_angerechnet = $row->ects_angerechnet;
				$io->ects_erworben = $row->ects_erworben;
				$io->herkunftsland_code = $row->herkunftsland_code;

				$this->result[] = $io;
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
	 * Liefert alle Incoming/Outgoing
	 * Eintraege eines Studenten, die
	 * innerhalb des gewuenschten
	 * Zeitraums beginnen
	 * @param $uid
	 * @param $von
	 * @param $bis
	 * @return true wenn ok, false wenn fehler
	 */
	public function getIOForPeriod($uid, $von, $bis)
	{
		$qry = "SELECT	tbl_bisio.*,
						tbl_mobilitaetsprogramm.kurzbz as mobilitaetsprogramm_kurzbz
				FROM
					bis.tbl_bisio,
					bis.tbl_mobilitaetsprogramm
				WHERE
					student_uid=".$this->db_add_param($uid)." AND
					tbl_mobilitaetsprogramm.mobilitaetsprogramm_code=tbl_bisio.mobilitaetsprogramm_code AND
					tbl_bisio.von BETWEEN ".$this->db_add_param($von)." AND ".$this->db_add_param($bis)."
				ORDER BY bis;";

		if ($this->db_query($qry))
		{
			while ($row = $this->db_fetch_object())
			{
				$io = new bisio();

				$io->bisio_id = $row->bisio_id;
				$io->mobilitaetsprogramm_code = $row->mobilitaetsprogramm_code;
				$io->mobilitaetsprogramm_kurzbz = $row->mobilitaetsprogramm_kurzbz;
				$io->nation_code = $row->nation_code;
				$io->von = $row->von;
				$io->bis = $row->bis;
				$io->student_uid = $row->student_uid;
				$io->updateamum = $row->updateamum;
				$io->updatevon = $row->updatevon;
				$io->insertamum = $row->insertamum;
				$io->insertvon = $row->insertvon;
				$io->ext_id = $row->ext_id;
				$io->ort = $row->ort;
				$io->universitaet = $row->universitaet;
				$io->lehreinheit_id = $row->lehreinheit_id;
				$io->ects_angerechnet = $row->ects_angerechnet;
				$io->ects_erworben = $row->ects_erworben;
				$io->herkunftsland_code = $row->herkunftsland_code;

				$this->result[] = $io;
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
	 * Laedt alle Foerderungen
	 */
	public function getFoerderungen($bisio_id = null)
	{
		if (is_null($bisio_id))
		{
			$qry = 'SELECT * FROM bis.tbl_aufenthaltfoerderung ORDER BY aufenthaltfoerderung_code;';
		}
		else
		{
			$qry = 'SELECT
						*
					FROM
						bis.tbl_aufenthaltfoerderung
						JOIN bis.tbl_bisio_aufenthaltfoerderung USING(aufenthaltfoerderung_code)
					WHERE
						tbl_bisio_aufenthaltfoerderung.bisio_id='.$this->db_add_param($bisio_id, FHC_INTEGER).'
					ORDER BY aufenthaltfoerderung_code;';
		}

		if ($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$io = new bisio();

				$io->aufenthaltfoerderung_code = $row->aufenthaltfoerderung_code;
				$io->bezeichnung = $row->bezeichnung;

				$this->result[] = $io;
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
	 * Laedt alle Zwecke
	 */
	public function getZweck($bisio_id = null, $outgoing = null, $incoming = null)
	{
		if (is_null($bisio_id))
		{
			$qry = 'SELECT * FROM bis.tbl_zweck WHERE 1=1';
			if ($outgoing === true)
				$qry .= " AND outgoing = true";
			if ($incoming === true)
				$qry .= " AND incoming = true";
			$qry .= ' ORDER BY zweck_code;';
		}
		else
		{
			$qry = 'SELECT
						*
					FROM
						bis.tbl_zweck
						JOIN bis.tbl_bisio_zweck USING(zweck_code)
					WHERE
						tbl_bisio_zweck.bisio_id='.$this->db_add_param($bisio_id, FHC_INTEGER).'
					ORDER BY zweck_code;';
		}

		if ($this->db_query($qry))
		{
			while ($row = $this->db_fetch_object())
			{
				$io = new bisio();

				$io->zweck_code = $row->zweck_code;
				$io->kurzbz = $row->kurzbz;
				$io->bezeichnung = $row->bezeichnung;

				$this->result[] = $io;
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
	 * Prueft ob ein Zweck bereits zu einem Auslandssemester zugeordnet ist
	 * @param $bisio_id ID des Auslandssemester Eintrages
	 * @param $zweck_code Code des Zweck
	 * @return true wenn vorhanden, false wenn nicht.
	 */
	public function ZweckExists($bisio_id, $zweck_code)
	{
		$qry = "
			SELECT
				*
			FROM
				bis.tbl_bisio_zweck
			WHERE
				bisio_id = ".$this->db_add_param($bisio_id, FHC_INTEGER)."
				AND zweck_code = ".$this->db_add_param($zweck_code);

		if ($result = $this->db_query($qry))
		{
			if ($this->db_num_rows($result) > 0)
				return true;
			else
				return false;
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Daten';
			return false;
		}
	}

	/**
	 * Speichert einen Zweck zu einem Auslandssemester
	 * @return true wenn erfolgreich, false im Fehlerfall
	 */
	public function saveZweck()
	{
		if (!$this->ZweckExists($this->bisio_id, $this->zweck_code))
		{
			$qry = 'INSERT INTO bis.tbl_bisio_zweck (bisio_id, zweck_code) VALUES('.
				 $this->db_add_param($this->bisio_id, FHC_INTEGER).', '.
				 $this->db_add_param($this->zweck_code).');';

			if ($this->db_query($qry))
			{
				return true;
			}
			else
			{
				$this->errormsg = 'Fehler beim Speichern der Daten';
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Eintrag ist bereits zugeordnet';
			return false;
		}
	}

	/**
	 * Entfernt einen Zweck zu einem Auslandssemester
	 * @return true wenn erfolgreich, false im Fehlerfall
	 */
	public function deleteZweck()
	{
		$qry = '
			DELETE FROM
				bis.tbl_bisio_zweck
			WHERE
				bisio_id = '.$this->db_add_param($this->bisio_id, FHC_INTEGER).'
			 	AND zweck_code = '.$this->db_add_param($this->zweck_code).';';

		if ($this->db_query($qry))
		{
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Löschen der Daten';
			return false;
		}
	}

	/**
	 * Prueft ob eine Foerderung bereits zu einem Auslandssemester zugeordnet ist
	 * @param $bisio_id ID des Auslandssemester Eintrages
	 * @param $aufenthaltfoerderung_code Code der Foerderung
	 * @return true wenn vorhanden, false wenn nicht.
	 */
	public function AufenthaltFoerderungExists($bisio_id, $aufenthaltfoerderung_code)
	{
		$qry = "
			SELECT
				*
			FROM
				bis.tbl_bisio_aufenthaltfoerderung
			WHERE
				bisio_id = ".$this->db_add_param($bisio_id, FHC_INTEGER)."
				AND aufenthaltfoerderung_code = ".$this->db_add_param($aufenthaltfoerderung_code, FHC_INTEGER);

		if ($result = $this->db_query($qry))
		{
			if ($this->db_num_rows($result) > 0)
				return true;
			else
				return false;
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Daten';
			return false;
		}
	}

	/**
	 * Speichert einen Zweck zu einem Auslandssemester
	 * @return true wenn erfolgreich, false im Fehlerfall
	 */
	public function saveAufenthaltFoerderung()
	{
		if ($this->aufenthaltfoerderung_code == '' || !is_numeric($this->aufenthaltfoerderung_code))
		{
			$this->errormsg = 'Aufenthalt Förderung ist ungültig';
			return false;
		}
		if ($this->bisio_id == '' || !is_numeric($this->bisio_id))
		{
			$this->errormsg = 'Bisio_id ist ungültig';
			return false;
		}

		if (!$this->AufenthaltFoerderungExists($this->bisio_id, $this->aufenthaltfoerderung_code))
		{
			$qry = 'INSERT INTO bis.tbl_bisio_aufenthaltfoerderung (bisio_id, aufenthaltfoerderung_code) VALUES('.
				 $this->db_add_param($this->bisio_id, FHC_INTEGER).', '.
				 $this->db_add_param($this->aufenthaltfoerderung_code).');';

			if ($this->db_query($qry))
			{
				return true;
			}
			else
			{
				$this->errormsg = 'Fehler beim Speichern der Daten';
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Eintrag ist bereits zugeordnet';
			return false;
		}
	}

	/**
	 * Entfernt eine Foerderung zu einem Auslandssemester
	 * @return true wenn erfolgreich, false im Fehlerfall
	 */
	public function deleteAufenthaltFoerderung()
	{
		$qry = '
			DELETE FROM
				bis.tbl_bisio_aufenthaltfoerderung
			WHERE
				bisio_id = '.$this->db_add_param($this->bisio_id, FHC_INTEGER).'
			 	AND aufenthaltfoerderung_code = '.$this->db_add_param($this->aufenthaltfoerderung_code, FHC_INTEGER).';';

		if ($this->db_query($qry))
		{
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Löschen der Daten';
			return false;
		}
	}
}
?>
