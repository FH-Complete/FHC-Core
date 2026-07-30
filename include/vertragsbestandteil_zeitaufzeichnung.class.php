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

class vertragsbestandteil_zeitaufzeichnung extends basis_db
{
	public $new;				// boolean
	public $result = array();

	//Tabellenspalten
	public $vertragsbestandteil_id;	// integer
	public $zeitaufzeichnung;	// bool
	public $azgrelevant;	// bool
	public $homeoffice;					// bool
	public $zeitmodell_id;					// integer
	public $von;					// date
	public $bis;					// date

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
	 * Speichert den Vertragsbestandteil in die Datenbank
	 * Wenn $new auf true gesetzt ist wird ein neuer Datensatz
	 * angelegt, ansonsten der Datensatz upgedated
	 *
	 * @return true wenn erfolgreich, false im Fehlerfall
	 */
	public function save()
	{
		if($this->new)
		{
			$qry = "INSERT INTO hr.tbl_vertragsbestandteil_zeitaufzeichnung (zeitaufzeichnung, azgrelevant, homeoffice, zeitmodell_id)
					VALUES(".
					$this->db_add_param($this->zeitaufzeichnung, FHC_BOOLEAN).",".
					$this->db_add_param($this->azgrelevant, FHC_BOOLEAN).",".
					$this->db_add_param($this->homeoffice, FHC_BOOLEAN).",".
					$this->db_add_param($this->zeitmodell_id).');';
		}
		else
		{
			$qry = 'UPDATE hr.tbl_vertragsbestandteil_zeitaufzeichnung SET'.
					' zeitaufzeichnung='.$this->db_add_param($this->zeitaufzeichnung, FHC_BOOLEAN).','.
					' azgrelevant='.$this->db_add_param($this->azgrelevant, FHC_BOOLEAN).','.
					' homeoffice='.$this->db_add_param($this->homeoffice, FHC_BOOLEAN).','.
					' zeitmodell_id='.$this->db_add_param($this->zeitmodell_id, FHC_INTEGER).
					' WHERE vertragsbestandteil_id='.$this->db_add_param($this->vertragsbestandteil_id, FHC_INTEGER);
		}

		if($this->db_query($qry))
		{
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern des Studiensemesters';
			return false;
		}
	}

	/**
	 * Holt alle Vertragsbestandteile ab einem bestimmten Startdatum.
	 *
	 * @param $mitarbeiter_uid
	 * @param $startDate
	 * @param $order
	 * @param null $limit
	 * @return bool
	 */
	public function getFromStartdate($mitarbeiter_uid, $startDate, $order = 'ASC', $limit = null)
	{
		if ($order !== 'ASC' && $order !== 'DESC')
		{
			$this->errormsg = 'Fehler beim Laden der Daten';
			return false;
		}

		$qry = 'SELECT
					vbt.vertragsbestandteil_id, vbt.dienstverhaeltnis_id, vbt.vertragsbestandteiltyp_kurzbz,
					vbt.von, vbt.bis, vbt.insertamum, vbt.insertvon, vbt.updateamum, vbt.updatevon,
					vbtza.zeitaufzeichnung, vbtza.azgrelevant, vbtza.homeoffice, vbtza.zeitmodell_id
				FROM hr.tbl_vertragsbestandteil_zeitaufzeichnung vbtza
				JOIN hr.tbl_vertragsbestandteil vbt USING (vertragsbestandteil_id)
				JOIN hr.tbl_dienstverhaeltnis dv USING (dienstverhaeltnis_id)
				-- Dienstverhältnis(se) des Mitarbeiters
				WHERE dv.mitarbeiter_uid = '. $this->db_add_param($mitarbeiter_uid). '
				-- Zeitaufzeichnungspflichtig
				AND zeitaufzeichnung = TRUE
				-- Vertragsbestandteile sind aktuell
				AND (vbt.bis IS NULL OR vbt.bis >= '. $this->db_add_param($startDate).')
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
					$vtb = new vertragsbestandteil_zeitaufzeichnung();
					$vtb->vertragsbestandteil_id = $row->vertragsbestandteil_id;
					$vtb->zeitaufzeichnung = $vtb->db_parse_bool($row->zeitaufzeichnung);
					$vtb->azgrelevant = $vtb->db_parse_bool($row->azgrelevant);
					$vtb->homeoffice = $vtb->db_parse_bool($row->homeoffice);
					$vtb->zeitmodell_id = $row->zeitmodell_id;
					$vtb->von = $row->von;
					$vtb->bis = $row->bis;
					$this->result[] = $vtb;
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
