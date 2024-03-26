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
 * Authors: Cristina Hainberger <cristina.hainberger@technikum-wien.at>,
 */
/**
 * Klasse Vertragsbestandteil
 * @create 29.03.2023
 */
require_once(dirname(__FILE__).'/basis_db.class.php');

class vertragsbestandteil extends basis_db
{
	public $new;				// boolean
	public $result = array();

	//Tabellenspalten
	public $vertragsbestandteil_id;	// serial
	public $dienstverhaeltnis_id;	// integer
	public $vertragsbestandteiltyp_kurzbz;	// varchar(32)
	public $von;					// date
	public $bis;					// date
	public $insertamum;				// timestamp
	public $insertvon;				// varchar(32)
	public $updateamum;				// timestamp
	public $updatevon;				// varchar(32)

	/**
	 * Konstruktor
	 * @param $vertragsbestandteil_id ID des Vertragsbestandteils, der geladen werden soll (Default=null)
	 */
	public function __construct($vertragsbestandteil_id = null)
	{
		parent::__construct();

		if($vertragsbestandteil_id != null)
			$this->load($vertragsbestandteil_id);
	}

	/**
	 * Laedt den Vertragsbestandteil mit der uebergebenen ID.
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function load($vertragsbestandteil_id)
	{
		$qry = "SELECT * FROM hr.tbl_vertragsbestandteil WHERE vertragsbestandteil_id = ". $this->db_add_param($vertragsbestandteil_id, FHC_INTEGER);

		if (!$this->db_query($qry))
		{
			$this->errormsg = 'Fehler bei einer Datenbankabfrage';
			return false;
		}

		if($row = $this->db_fetch_object())
		{
			$this->vertragsbestandteil_id = $row->vertragsbestandteil_id;
			$this->dienstverhaeltnis_id = $row->dienstverhaeltnis_id;
			$this->vertragsbestandteiltyp_kurzbz = $row->vertragsbestandteiltyp_kurzbz;
			$this->von = $row->von;
			$this->bis = $row->bis;
			$this->insertvon = $row->insertvon;
			$this->updateamum = $row->updateamum;
			$this->updatevon = $row->updatevon;
		}
		else
		{
			$this->errormsg = 'Es ist kein Datensatz mit dieser ID vorhanden';
			return false;
		}

		return true;
	}

	/**
	 * Prueft ob MitarbeiterIn im Monat des uebergebenen $datums zeitaufzeichnungspflichtig ist.
	 * Wenn kein Datum übergeben wird, wird das heutige Datum gesetzt.
	 *
	 * @param $mitarbeiter_uid
	 * @param string $timestamp DD-MM-YYYY
	 * @return bool
	 */
	public function isZaPflichtig($mitarbeiter_uid, $datum = null)
	{

		$timestamp = is_null($datum) ? 'NOW()' :  '(date('. $this->db_add_param($datum).'))';

		$qry = 'SELECT
					vbt.vertragsbestandteil_id, vbt.dienstverhaeltnis_id, vbt.vertragsbestandteiltyp_kurzbz,
					vbt.von, vbt.bis, vbt.insertamum, vbt.insertvon, vbt.updateamum, vbt.updatevon,
					vbtza.zeitaufzeichnung, vbtza.azgrelevant, vbtza.homeoffice
				FROM hr.tbl_vertragsbestandteil_zeitaufzeichnung vbtza
				JOIN hr.tbl_vertragsbestandteil vbt USING (vertragsbestandteil_id)
				JOIN hr.tbl_dienstverhaeltnis dv USING (dienstverhaeltnis_id)
				-- Dienstverhältnis(se) des Mitarbeiters
				WHERE dv.mitarbeiter_uid = '. $this->db_add_param($mitarbeiter_uid). '
				-- Zeitaufzeichnungspflichtig...
				AND zeitaufzeichnung = TRUE
				-- ...im aktuellen Monat (default) oder im Monat des übergebenen $datums
				AND ((date_trunc(\'month\', '. $timestamp. ')::date < vbt.bis AND (date_trunc(\'month\', '. $timestamp. ') + interval \'1 month - 1 day\')::date > vbt.von) OR (vbt.bis IS NULL AND (date_trunc(\'month\', '. $timestamp. ') + interval \'1 month - 1 day\')::date > vbt.von))
				-- Vorerst nur check, ob zumindest eine aufrechte Zeitaufzeichnungspflicht. Später Unterscheidung nach Dienstverhältnis.
				ORDER BY vbt.von DESC --aktuellster
				LIMIT 1';

		if ($result = $this->db_query($qry))
		{
			if ($this->db_num_rows($result) > 0)
			{
				$this->result = array();

				while ($row = $this->db_fetch_object())
				{
					$obj = new stdClass();

					$obj->vertragsbestandteil_id = $row->vertragsbestandteil_id;
					$obj->dienstverhaeltnis_id = $row->dienstverhaeltnis_id;
					$obj->vertragsbestandteiltyp_kurzbz = $row->vertragsbestandteiltyp_kurzbz;
					$obj->von = $row->von;
					$obj->bis = $row->bis;
					$obj->insertamum = $row->insertamum;
					$obj->insertvon = $row->insertvon;
					$obj->updateamum = $row->updateamum;
					$obj->updatevon = $row->updatevon;
					$obj->zeitaufzeichnung = $row->zeitaufzeichnung;
					$obj->azgrelevant = $row->azgrelevant;
					$obj->homeoffice = $row->homeoffice;

					$this->result[] = $obj;
				}
				return true;
			}
			else
			{
				return false;
			}
		}
		else
		{
			$this->errormsg = "Fehler bei der Abfrage aufgetreten";
			return false;
		}

	}

	/**
	 * Holt alle Vertragsbestandteile, die eine Zeitaufzeichnungspflicht haben.
	 *
	 * Set order DESC limit 1 to retrieve only most recent Vertragsbestandteil.
	 * Set order ASC limit 1 to retrieve only first Vertragsbestandteil.
	 *
	 * @param $mitarbeiter_uid
	 * @param string $order
	 * @param null $limit
	 * @return bool
	 */
	public function getZaPflichtig($mitarbeiter_uid, $order = 'DESC', $limit = null)
	{
		if ($order !== 'ASC' && $order !== 'DESC')
		{
			$this->errormsg = 'Fehler beim Laden der Daten';
			return false;
		}

		$qry = 'SELECT
					vbt.vertragsbestandteil_id, vbt.dienstverhaeltnis_id, vbt.vertragsbestandteiltyp_kurzbz,
					vbt.von, vbt.bis, vbt.insertamum, vbt.insertvon, vbt.updateamum, vbt.updatevon,
					vbtza.zeitaufzeichnung, vbtza.azgrelevant, vbtza.homeoffice
				FROM hr.tbl_vertragsbestandteil_zeitaufzeichnung vbtza
				JOIN hr.tbl_vertragsbestandteil vbt USING (vertragsbestandteil_id)
				JOIN hr.tbl_dienstverhaeltnis dv USING (dienstverhaeltnis_id)
				-- Dienstverhältnis(se) des Mitarbeiters
				WHERE dv.mitarbeiter_uid = '. $this->db_add_param($mitarbeiter_uid). '
				-- Zeitaufzeichnungspflichtig
				AND zeitaufzeichnung = TRUE
				-- Vertragsbestandteile sind aktuell, liegen nach dem GoLive und starten vor dem aktuellen Monatsletzten
				AND(
					(COALESCE(vbt.bis, NOW()::date) > '. $this->db_add_param(CASETIME_TIMESHEET_GOLIVE). '::date) AND
					(vbt.von < (date_trunc(\'month\', NOW()) + interval \'1 month - 1 day\')::date)
				)
				ORDER BY vbt.von ' . $order;


		if (!is_null($limit))
		{
			$qry .= ' LIMIT ' . $this->db_add_param($limit, FHC_INTEGER);
		}

		if ($result = $this->db_query($qry))
		{
			if ($this->db_num_rows($result) > 0)
			{
				$this->result = array();

				while ($row = $this->db_fetch_object())
				{
					$obj = new stdClass();

					$obj->vertragsbestandteil_id = $row->vertragsbestandteil_id;
					$obj->dienstverhaeltnis_id = $row->dienstverhaeltnis_id;
					$obj->vertragsbestandteiltyp_kurzbz = $row->vertragsbestandteiltyp_kurzbz;
					$obj->von = $row->von;
					$obj->bis = $row->bis;
					$obj->insertamum = $row->insertamum;
					$obj->insertvon = $row->insertvon;
					$obj->updateamum = $row->updateamum;
					$obj->updatevon = $row->updatevon;
					$obj->zeitaufzeichnung = $row->zeitaufzeichnung;
					$obj->azgrelevant = $row->azgrelevant;
					$obj->homeoffice = $row->homeoffice;

					$this->result[] = $obj;
				}
				return true;
			}
			else
			{
				return false;
			}
		}
		else
		{
			$this->errormsg = "Fehler bei der Abfrage aufgetreten";
			return false;
		}

	}

	/**
	 * Prueft ob MitarbeiterIn am Tag des uebergebenen $datums AZG-relevant ist.
	 * Wenn kein Datum übergeben wird, wird das heutige Datum gesetzt.
	 *
	 * @param $mitarbeiter_uid
	 * @param string $timestamp DD-MM-YYYY
	 * @return bool
	 */
	public function isAzgRelevant($mitarbeiter_uid, $datum = null)
	{

		$timestamp = is_null($datum) ? 'NOW()' :  '(date('. $this->db_add_param($datum).'))';

		$qry = 'SELECT
					vbt.vertragsbestandteil_id, vbt.dienstverhaeltnis_id, vbt.vertragsbestandteiltyp_kurzbz,
					vbt.von, vbt.bis, vbt.insertamum, vbt.insertvon, vbt.updateamum, vbt.updatevon,
					vbtza.zeitaufzeichnung, vbtza.azgrelevant, vbtza.homeoffice
				FROM hr.tbl_vertragsbestandteil_zeitaufzeichnung vbtza
				JOIN hr.tbl_vertragsbestandteil vbt USING (vertragsbestandteil_id)
				JOIN hr.tbl_dienstverhaeltnis dv USING (dienstverhaeltnis_id)
				-- Dienstverhältnis(se) des Mitarbeiters
				WHERE dv.mitarbeiter_uid = '. $this->db_add_param($mitarbeiter_uid). '
				-- AZG-relevant...
				AND azgrelevant = TRUE
				-- ...am aktuellen Tag (default) oder am Tag des übergebenen $datums
				AND (
					(' . $timestamp . '::date BETWEEN vbt.von AND vbt.bis)
					OR
					(vbt.bis IS NULL AND ' . $timestamp . '::date > vbt.von)
				)
				ORDER BY vbt.von DESC --zur Sicherheit: aktuellster
				LIMIT 1';

		if ($result = $this->db_query($qry))
		{
			if ($this->db_num_rows($result) > 0)
			{
				$this->result = array();

				while ($row = $this->db_fetch_object())
				{
					$obj = new stdClass();

					$obj->vertragsbestandteil_id = $row->vertragsbestandteil_id;
					$obj->dienstverhaeltnis_id = $row->dienstverhaeltnis_id;
					$obj->vertragsbestandteiltyp_kurzbz = $row->vertragsbestandteiltyp_kurzbz;
					$obj->von = $row->von;
					$obj->bis = $row->bis;
					$obj->insertamum = $row->insertamum;
					$obj->insertvon = $row->insertvon;
					$obj->updateamum = $row->updateamum;
					$obj->updatevon = $row->updatevon;
					$obj->zeitaufzeichnung = $row->zeitaufzeichnung;
					$obj->azgrelevant = $row->azgrelevant;
					$obj->homeoffice = $row->homeoffice;

					$this->result[] = $obj;
				}
				return true;
			}
			else
			{
				return false;
			}
		}
		else
		{
			$this->errormsg = "Fehler bei der Abfrage aufgetreten";
			return false;
		}

	}

	/**
	 * Prueft ob MitarbeiterIn am Tag des uebergebenen $datums Homeoffice aktiv gesetzt hat.
	 * Wenn kein Datum übergeben wird, wird das heutige Datum gesetzt.
	 *
	 * @param $mitarbeiter_uid
	 * @param string $timestamp DD-MM-YYYY
	 * @return bool
	 */
	public function hasHomeoffice($mitarbeiter_uid, $datum = null)
	{

		$timestamp = is_null($datum) ? 'NOW()' :  '(date('. $this->db_add_param($datum).'))';

		$qry = 'SELECT
					vbt.vertragsbestandteil_id, vbt.dienstverhaeltnis_id, vbt.vertragsbestandteiltyp_kurzbz,
					vbt.von, vbt.bis, vbt.insertamum, vbt.insertvon, vbt.updateamum, vbt.updatevon,
					vbtza.zeitaufzeichnung, vbtza.azgrelevant, vbtza.homeoffice
				FROM hr.tbl_vertragsbestandteil_zeitaufzeichnung vbtza
				JOIN hr.tbl_vertragsbestandteil vbt USING (vertragsbestandteil_id)
				JOIN hr.tbl_dienstverhaeltnis dv USING (dienstverhaeltnis_id)
				-- Dienstverhältnis(se) des Mitarbeiters
				WHERE dv.mitarbeiter_uid = '. $this->db_add_param($mitarbeiter_uid). '
				-- Homeoffice...
				AND homeoffice = TRUE
				-- ...am aktuellen Tag (default) oder am Tag des übergebenen $datums
				AND (
					(' . $timestamp . '::date BETWEEN vbt.von AND vbt.bis)
					OR
					(vbt.bis IS NULL AND ' . $timestamp . '::date >= vbt.von)
				)
				ORDER BY vbt.von DESC -- Zur Sicherheit: aktuellster
				LIMIT 1';

		if ($result = $this->db_query($qry))
		{
			if ($this->db_num_rows($result) > 0)
			{
				$this->result = array();

				while ($row = $this->db_fetch_object())
				{
					$obj = new stdClass();

					$obj->vertragsbestandteil_id = $row->vertragsbestandteil_id;
					$obj->dienstverhaeltnis_id = $row->dienstverhaeltnis_id;
					$obj->vertragsbestandteiltyp_kurzbz = $row->vertragsbestandteiltyp_kurzbz;
					$obj->von = $row->von;
					$obj->bis = $row->bis;
					$obj->insertamum = $row->insertamum;
					$obj->insertvon = $row->insertvon;
					$obj->updateamum = $row->updateamum;
					$obj->updatevon = $row->updatevon;
					$obj->zeitaufzeichnung = $row->zeitaufzeichnung;
					$obj->azgrelevant = $row->azgrelevant;
					$obj->homeoffice = $row->homeoffice;

					$this->result[] = $obj;
				}
				return true;
			}
			else
			{
				return false;
			}
		}
		else
		{
			$this->errormsg = "Fehler bei der Abfrage aufgetreten";
			return false;
		}

	}

	/**
	 * Prueft ob MitarbeiterIn im Monat des uebergebenen $datums einen Allin Vertragsbestandteil hat.
	 * Wenn kein Datum übergeben wird, wird das heutige Datum gesetzt.
	 *
	 * @param $mitarbeiter_uid
	 * @param string $datum
	 * @return bool
	 */
	public function isAllin($mitarbeiter_uid, $datum = null)
	{
		$timestamp = is_null($datum) ? 'NOW()' :  '(date('. $this->db_add_param($datum).'))';

		$qry = 'SELECT
       				vbt.vertragsbestandteil_id, vbt.dienstverhaeltnis_id, vbt.vertragsbestandteiltyp_kurzbz,
					vbt.von, vbt.bis, vbt.insertamum, vbt.insertvon, vbt.updateamum, vbt.updatevon,
       				vbtft.freitexttyp_kurzbz, vbtftt.bezeichnung, vbtft.titel, vbtft.anmerkung
				FROM hr.tbl_vertragsbestandteil_freitext vbtft
				JOIN hr.tbl_vertragsbestandteil vbt USING (vertragsbestandteil_id)
				JOIN hr.tbl_dienstverhaeltnis dv USING (dienstverhaeltnis_id)
				JOIN hr.tbl_vertragsbestandteil_freitexttyp vbtftt USING (freitexttyp_kurzbz)
				-- Dienstverhältnis(se) des Mitarbeiters
				WHERE dv.mitarbeiter_uid = '. $this->db_add_param($mitarbeiter_uid). '
				-- All-In
				AND freitexttyp_kurzbz = \'allin\'
				-- Vertragsbestandteil Freitext ist im Monat des übergebenen $datums
				AND ((date_trunc(\'month\', '. $timestamp. ')::date < vbt.bis AND (date_trunc(\'month\', '. $timestamp. ') + interval \'1 month - 1 day\')::date > vbt.von) OR (vbt.bis IS NULL AND (date_trunc(\'month\', '. $timestamp. ') + interval \'1 month - 1 day\')::date > vbt.von))
				-- Vorerst nur check, ob zumindest ein aufrechter Allin Vertragsbestandteil. Später Unterscheidung nach Dienstverhältnis.
				--ORDER BY vbt.von DESC -- aktuellster
				LIMIT 1';

		if ($result = $this->db_query($qry))
		{
			if ($this->db_num_rows($result) > 0)
			{
				$this->result = array();

				while ($row = $this->db_fetch_object())
				{
					$obj = new stdClass();

					$obj->vertragsbestandteil_id = $row->vertragsbestandteil_id;
					$obj->dienstverhaeltnis_id = $row->dienstverhaeltnis_id;
					$obj->vertragsbestandteiltyp_kurzbz = $row->vertragsbestandteiltyp_kurzbz;
					$obj->von = $row->von;
					$obj->bis = $row->bis;
					$obj->insertamum = $row->insertamum;
					$obj->insertvon = $row->insertvon;
					$obj->updateamum = $row->updateamum;
					$obj->updatevon = $row->updatevon;
					$obj->freitexttyp_kurzbz = $row->freitexttyp_kurzbz;
					$obj->bezeichnung = $row->bezeichnung;
					$obj->titel = $row->titel;
					$obj->anmerkung = $row->anmerkung;

					$this->result[] = $obj;
				}
				return true;
			}
			else
			{
				return false;
			}
		}
		else
		{
			$this->errormsg = "Fehler bei der Abfrage aufgetreten";
			return false;
		}
	}

	/**
	 * Prüft, ob MitarbeiterIn zum Abfragedatum karenziert ist.
	 * Wenn kein Datum übergeben wird, wird das heutige Datum gesetzt.
	 *
	 * @param $mitarbeiter_uid
	 * @param null $datum
	 * @return bool
	 */
	public function isKarenziert($mitarbeiter_uid, $datum = null)
	{
		$timestamp = is_null($datum) ? 'NOW()' :  '(date('. $this->db_add_param($datum).'))';

		$qry = '
				SELECT
					1
				FROM
					hr.tbl_vertragsbestandteil vbt
				JOIN
					hr.tbl_dienstverhaeltnis dv USING (dienstverhaeltnis_id)
				WHERE
				    dv.mitarbeiter_uid = '. $this->db_add_param($mitarbeiter_uid). '
				AND
					vbt.vertragsbestandteiltyp_kurzbz = \'karenz\'
				AND
					vbt.von::date <= '. $timestamp. '::date
				AND
					vbt.bis::date >= '. $timestamp. '::date
		';


		if ($result = $this->db_query($qry))
		{
			if ($this->db_num_rows($result) > 0)
			{
				return true;
			}
			else
			{
				return false;
			}
		}
		else
		{
			$this->errormsg = "Fehler bei der Abfrage aufgetreten";
			return false;
		}
	}

	/**
	 * Arbeits-Wochenstunden eines/r MitarbeiterIn im Monat des uebergebenen $datums. Karenzierte Dienstverhältnisse
	 * werden nicht zurückgegeben. Dafür aber ein eventuelles 2.DV mit geringfügiger Beschäftigung neben der Karenz.
	 * Wenn kein Datum übergeben wird, wird das heutige Datum gesetzt.
	 *
	 * @param $mitarbeiter_uid
	 * @param null $datum
	 * @return bool
	 */
	public function getWochenstunden($mitarbeiter_uid, $datum = null)
	{
		$timestamp = is_null($datum) ? 'NOW()' :  '(date('. $this->db_add_param($datum).'))';

		$qry = 'SELECT
					vbtstd.vertragsbestandteil_id,
				   	vbtstd.wochenstunden,
				   	vbtstd.teilzeittyp_kurzbz
				FROM hr.tbl_vertragsbestandteil_stunden vbtstd
				JOIN hr.tbl_vertragsbestandteil vbt USING (vertragsbestandteil_id)
				JOIN hr.tbl_dienstverhaeltnis dv USING (dienstverhaeltnis_id)
				-- Dienstverhältnis(se) des Mitarbeiters
				WHERE dv.mitarbeiter_uid = '. $this->db_add_param($mitarbeiter_uid). '
				-- Vertragsbestandteil ist im Monat des übergebenen $datums
				AND ((date_trunc(\'month\', '. $timestamp. ')::date < vbt.bis AND (date_trunc(\'month\', '. $timestamp. ') + interval \'1 month - 1 day\')::date > vbt.von) OR (vbt.bis IS NULL AND (date_trunc(\'month\', '. $timestamp. ') + interval \'1 month - 1 day\')::date > vbt.von))
				-- DV mit Vertragsbestandteile Karenz herausnehmen, weil die Wochenstunden dieser DV dann ruhen
				AND (
					SELECT
						COUNT(*) AS karenzen
					FROM
						hr.tbl_vertragsbestandteil vbt
					WHERE
						vbt.dienstverhaeltnis_id = dv.dienstverhaeltnis_id
					AND
						vbt.vertragsbestandteiltyp_kurzbz = \'karenz\'
					AND
						vbt.von::date <= '. $timestamp. '::date
					AND
						vbt.bis::date >= '. $timestamp. '::date
					) = 0
				ORDER BY vbt.von DESC -- aktuellster
				LIMIT 1';

		if ($result = $this->db_query($qry))
		{
			if ($this->db_num_rows($result) > 0)
			{
				$this->result = array();

				while ($row = $this->db_fetch_object())
				{
					$obj = new stdClass();

					$obj->vertragsbestandteil_id = $row->vertragsbestandteil_id;
					$obj->wochenstunden = $row->wochenstunden;
					$obj->teilzeittyp_kurzbz = $row->teilzeittyp_kurzbz;

					$this->result[] = $obj;
				}
				return true;
			}
			else
			{
				return false;
			}
		}
		else
		{
			$this->errormsg = "Fehler bei der Abfrage aufgetreten";
			return false;
		}
	}
}
?>
