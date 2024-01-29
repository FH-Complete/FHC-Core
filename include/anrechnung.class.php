<?php

/* Copyright (C) 2015 Technikum-Wien
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
 * Authors: Nikolaus Krondraf <nikolaus.krondraf@technikum-wien.at> and
 *          Andreas Moik <moik@technikum-wien.at>.
 */
require_once(dirname(__FILE__) . '/basis_db.class.php');
require_once(dirname(__FILE__) . '/datum.class.php');
require_once(dirname(__FILE__) . '/lehrveranstaltung.class.php');

class anrechnung extends basis_db
{

	public $errormsg;
	public $new;
	public $result;
	// Tabellenspalten
	public $anrechnung_id; // integer
	public $prestudent_id; // integer
	public $lehrveranstaltung_id; // integer
	public $begruendung_id; // integer
	public $lehrveranstaltung_id_kompatibel; // integer
	public $genehmigt_von; // varchar(32)
	public $insertamum;  // timestamp
	public $insertvon;  // varchar(32)
	public $updateamum;  // timestamp
	public $updatevon;  // varchar(32)
	public $begruendungen = array();

	/**
	 * Konstruktor - Laedt optional eine Anrechnung
	 * @param $anrechnung_id
	 */
	public function __construct($anrechnung_id = null)
	{
		parent::__construct();
		if ($anrechnung_id != null)
			$this->load($anrechnung_id);
	}

	public function validate()
	{
		if (!is_numeric($this->prestudent_id))
		{
			$this->errormsg = "Prestudent_id ist ungueltig";
			return false;
		}

		if (!is_numeric($this->lehrveranstaltung_id))
		{
			$this->errormsg = "Wählen Sie eine Lehrveranstaltung aus";
			return false;
		}

		if (!is_numeric($this->begruendung_id))
		{
			$this->errormsg = "Wählen Sie eine Begründung aus";
			return false;
		}

		if ($this->begruendung_id == "2" && !is_numeric($this->lehrveranstaltung_id_kompatibel))
		{
			$this->errormsg = "Wählen Sie eine kompatible Lehrveranstaltung aus";
			return false;
		}

		if (empty($this->genehmigt_von))
		{
			$this->errormsg = "Wählen Sie die Person aus, die die Anrechnung genehmigt hat";
			return false;
		}

		return true;
	}

	/**
	 * Lädt eine Anrechnung
	 * @param $anrechnung_id ID der Anrechnung
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function getAnrechnung($anrechnung_id)
	{
		$qry = "SELECT anrechnung_id, prestudent_id, lehrveranstaltung_id, begruendung_id, bezeichnung AS begruendung, "
			. "lehrveranstaltung_id_kompatibel, genehmigt_von, insertamum, insertvon, updateamum, updatevon "
			. "FROM lehre.tbl_anrechnung "
			. "JOIN lehre.tbl_anrechnung_begruendung USING (begruendung_id) "
			. "WHERE anrechnung_id = " . $this->db_add_param($anrechnung_id);

		if ($result = $this->db_query($qry))
		{
			if ($row = $this->db_fetch_object($result))
			{
				$datum = new datum();
				$lehrveranstaltung = new lehrveranstaltung($row->lehrveranstaltung_id);
				$row->insertamum = $datum->convertISODate($row->insertamum);
				$row->lehrveranstaltung_bez = $lehrveranstaltung->bezeichnung;

				if ($row->lehrveranstaltung_id_kompatibel != '')
				{
					$lehrveranstaltung = new lehrveranstaltung($row->lehrveranstaltung_id_kompatibel);
					$row->lehrveranstaltung_bez_kompatibel = $lehrveranstaltung->bezeichnung;
				}
				else
					$row->lehrveranstaltung_bez_kompatibel = null;

				$this->result[] = $row;
			}
			else
			{
				$this->errormsg = 'Es wurde kein Datensatz mit dieser ID gefunden';
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
	 * Speichert eine Anrechnung
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function save()
	{
		if (!$this->validate())
			return false;

		if ($this->new == "1")
		{
			// Neuen Datensatz anlegen
			$qry = 'INSERT INTO lehre.tbl_anrechnung (prestudent_id, lehrveranstaltung_id, begruendung_id, lehrveranstaltung_id_kompatibel, genehmigt_von, insertamum, insertvon, updateamum, updatevon) VALUES (' .
				$this->db_add_param($this->prestudent_id) . ', ' .
				$this->db_add_param($this->lehrveranstaltung_id) . ', ' .
				$this->db_add_param($this->begruendung_id) . ', ' .
				$this->db_add_param($this->lehrveranstaltung_id_kompatibel) . ', ' .
				$this->db_add_param($this->genehmigt_von) . ', ' .
				'NOW(),' .
				$this->db_add_param($this->insertvon) . ', ' .
				'NOW(),' .
				$this->db_add_param($this->updatevon) . ') RETURNING anrechnung_id;';
		}
		else
		{
			// Datensatz aktualisieren
			$qry = 'UPDATE lehre.tbl_anrechnung SET '
				. 'lehrveranstaltung_id = ' . $this->db_add_param($this->lehrveranstaltung_id) . ', '
				. 'begruendung_id = ' . $this->db_add_param($this->begruendung_id) . ', '
				. 'lehrveranstaltung_id_kompatibel = ' . $this->db_add_param($this->lehrveranstaltung_id_kompatibel) . ', '
				. 'genehmigt_von = ' . $this->db_add_param($this->genehmigt_von) . ', '
				. 'updateamum = NOW(), '
				. 'updatevon = ' . $this->db_add_param($this->updatevon) . ' '
				. 'WHERE anrechnung_id = ' . $this->db_add_param($this->anrechnung_id). ' RETURNING anrechnung_id;';
		}
		
		if ($this->db_query($qry))
		{
			$this->anrechnung_id = $this->db_fetch_object()->anrechnung_id;
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern der Anrechnung: ' . $this->db_last_error();
			return false;
		}
	}

	/**
	 * Gibt alle Anrechnungen eines Prestudenten zurück
	 * @param $prestudent_id
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function getAnrechnungPrestudent($prestudent_id, $lehrveranstaltung_id=null, $lehrveranstaltung_id_kompatibel=null)
	{
		$qry = "SELECT anrechnung_id, prestudent_id, lehrveranstaltung_id, begruendung_id, bezeichnung AS begruendung, "
			. "lehrveranstaltung_id_kompatibel, genehmigt_von , insertamum, insertvon, updateamum, updatevon "
			. "FROM lehre.tbl_anrechnung "
			. "JOIN lehre.tbl_anrechnung_begruendung USING (begruendung_id) "
			. "WHERE prestudent_id = " . $this->db_add_param($prestudent_id);
		
		if($lehrveranstaltung_id != NULL)
		{
		    $qry .= " AND lehrveranstaltung_id=".$this->db_add_param($lehrveranstaltung_id);
		}
		
		if($lehrveranstaltung_id_kompatibel != NULL)
		{
		    $qry .= " AND lehrveranstaltung_id_kompatibel=".$this->db_add_param($lehrveranstaltung_id_kompatibel);
		}
		
		$qry .= ";";

		if ($this->db_query($qry))
		{
			$datum = new datum();

			while ($row = $this->db_fetch_object())
			{
				$row->insertamum = $datum->convertISODate($row->insertamum);
				$lehrveranstaltung = new lehrveranstaltung($row->lehrveranstaltung_id);
				$row->lehrveranstaltung_bez = $lehrveranstaltung->bezeichnung;

				if ($row->lehrveranstaltung_id_kompatibel != '')
				{
					$lehrveranstaltung = new lehrveranstaltung($row->lehrveranstaltung_id_kompatibel);
					$row->lehrveranstaltung_bez_kompatibel = $lehrveranstaltung->bezeichnung;
				}
				else
					$row->lehrveranstaltung_bez_kompatibel = null;

				$this->result[] = $row;
			}

			return true;
		}
		else
		{
			$this->errormsg = 'Datensatz konnte nicht geladen werden';
			return false;
		}
	}

	/**
	 * Loescht eine Anrechnung
	 * @param $anrechnung_id
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function delete($anrechnung_id)
	{
		$qry = "DELETE FROM lehre.tbl_anrechnung_anrechnungstatus WHERE anrechnung_id = " . $this->db_add_param($anrechnung_id). "; ";
		$qry.= "DELETE FROM lehre.tbl_anrechnung WHERE anrechnung_id = " . $this->db_add_param($anrechnung_id);

		if ($this->db_query($qry))
		{
			return true;
		}
		else
		{
			$this->errormsg = 'Datensatz konnte nicht geloescht werden';
			return false;
		}
	}

	/**
	 * Gibt die Anzahl der Notizen für eine Anrechnung zurück
	 * @param $anrechung_id
	 * @return Anzahl der Notizen, false im Fehlerfall
	 */
	public function getAnzahlNotizen($anrechung_id)
	{
		$qry = "SELECT COUNT(*) AS anzahl "
		. "FROM public.tbl_notizzuordnung "
		. "WHERE anrechnung_id = " . $this->db_add_param($anrechung_id);

		if ($result = $this->db_query($qry))
		{
			if ($row = $this->db_fetch_object($result))
			{
				return $row->anzahl;
			}
			else
			{
				$this->errormsg = 'Daten konnten nicht geladen werden';
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Daten konnten nicht geladen werden';
			return false;
		}
	}

	/**
	 * Gibt alle möglichen Begründungen zurück
	 * @return array Array der Begründungen
	 */
	public function getAllBegruendung()
	{
		$qry = 'SELECT * FROM lehre.tbl_anrechnung_begruendung';

		if ($this->db_query($qry))
		{
		while ($row = $this->db_fetch_object())
		{
			$stdobj = new stdClass();
			$stdobj->begruendung_id = $row->begruendung_id;
			$stdobj->bezeichnung = $row->bezeichnung;
			array_push($this->begruendungen, $stdobj);
		}
		return true;
		}
		else
		{
			$this->errormsg = 'Daten konnten nicht geladen werden';
			return false;
		}
	}

	/**
	 * Lädt eine Anrechnung
	 * @param $anrechnung_id ID der Anrechnung
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function load($anrechnung_id)
	{
		$qry = "SELECT anrechnung_id, prestudent_id, lehrveranstaltung_id, begruendung_id, bezeichnung AS begruendung, "
		. "lehrveranstaltung_id_kompatibel, genehmigt_von, insertamum, insertvon, updateamum, updatevon "
		. "FROM lehre.tbl_anrechnung "
		. "JOIN lehre.tbl_anrechnung_begruendung USING (begruendung_id) "
		. "WHERE anrechnung_id = " . $this->db_add_param($anrechnung_id);

		if ($result = $this->db_query($qry))
		{
			if ($row = $this->db_fetch_object($result))
			{
				$this->anrechnung_id = $row->anrechnung_id;
				$this->prestudent_id = $row->prestudent_id;
				$this->lehrveranstaltung_id = $row->lehrveranstaltung_id;
				$this->lehrveranstaltung_id_kompatibel = $row->lehrveranstaltung_id_kompatibel;
				$this->begruendung_id = $row->begruendung_id;
				$this->begruendung = $row->begruendung;
				$this->genehmigt_von = $row->genehmigt_von;

				$datum = new datum();
				$lehrveranstaltung = new lehrveranstaltung($row->lehrveranstaltung_id);
				$this->insertamum = $datum->convertISODate($row->insertamum);
				$this->lehrveranstaltung_bez = $lehrveranstaltung->bezeichnung;

				if ($row->lehrveranstaltung_id_kompatibel != '')
				{
					$lehrveranstaltung = new lehrveranstaltung($row->lehrveranstaltung_id_kompatibel);
					$this->lehrveranstaltung_bez_kompatibel = $lehrveranstaltung->bezeichnung;
				}
				else
					$this->lehrveranstaltung_bez_kompatibel = null;
			}
			else
			{
				$this->errormsg = 'Es wurde kein Datensatz mit dieser ID gefunden';
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Daten';
			return false;
		}
	}
	
	public function getLastAnrechnungstatus($anrechnung_id)
	{
		$sprache = new sprache();
		$bezeichnung_mehrsprachig = $sprache->getSprachQuery('bezeichnung_mehrsprachig');
		
		$qry = '
			SELECT *, '. $bezeichnung_mehrsprachig. '
			FROM lehre.tbl_anrechnungstatus
			JOIN lehre.tbl_anrechnung_anrechnungstatus USING (status_kurzbz)
			WHERE anrechnung_id = ' . $this->db_add_param($anrechnung_id). '
			ORDER BY insertamum DESC
			LIMIT 1
		';
		
		if ($this->db_query($qry))
		{
			if ($row = $this->db_fetch_object())
			{
				$obj = new stdClass();
				$obj->anrechnungstatus_id = $row->anrechnungstatus_id;
				$obj->status_kurzbz = $row->status_kurzbz;
				$obj->bezeichnung_mehrsprachig = $sprache->parseSprachResult('bezeichnung_mehrsprachig', $row);

				$this->result[]= $obj;
				return true;
			}
			else
			{
				$this->errormsg = 'Daten konnten nicht geladen werden';
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Daten konnten nicht geladen werden';
			return false;
		}
	}
}
