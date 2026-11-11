<?php
/* Copyright (C) 2026 fhcomplete.org
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
 */

require_once(dirname(__FILE__). '/basis_db.class.php');
require_once(dirname(__FILE__). '/sprache.class.php');

/**
 * Karrierepfad: marketing specialisation of a Studiengang, linked to Studienpläne.
 * Not official: no printing it on a Zeugnis or report to BIS.
 */
class karrierepfad extends basis_db
{
	public $result = array();

	//Tabellenspalten
	public $karrierepfad_id;
	public $karrierepfad_kurzbz;
	public $karrierepfad_typ_kurzbz;
	public $studiengang_kz;
	public $bezeichnung_mehrsprachig;	// array, key = sprache
	public $sort;
	public $aktiv;
	public $insertamum;
	public $insertvon;
	public $updateamum;
	public $updatevon;

	/**
	 * @param integer $karrierepfad_id Karrierepfad to load (optional)
	 */
	public function __construct($karrierepfad_id = null)
	{
		parent::__construct();

		if ($karrierepfad_id != null)
			$this->load($karrierepfad_id);
	}

	/**
	 * Loads a Karrierepfad
	 * @param integer $karrierepfad_id
	 * @return boolean
	 */
	public function load($karrierepfad_id)
	{
		if (!is_numeric($karrierepfad_id))
		{
			$this->errormsg = 'Karrierepfad ID ist ungueltig';
			return false;
		}

		$sprache = new sprache();
		$qry = "SELECT *, ".$sprache->getSprachQuery('bezeichnung_mehrsprachig')."
				FROM public.tbl_karrierepfad
				WHERE karrierepfad_id=".$this->db_add_param($karrierepfad_id, FHC_INTEGER);

		if (!$this->db_query($qry))
		{
			$this->errormsg = 'Fehler beim Laden des Karrierepfads';
			return false;
		}

		if (!$row = $this->db_fetch_object())
		{
			$this->errormsg = 'Karrierepfad wurde nicht gefunden';
			return false;
		}

		$this->setFromRow($this, $row, $sprache);
		return true;
	}

	/**
	 * Loads a Karrierepfad by its kurzbz, the value of the URL parameter karrierepfad_kurzbz
	 * @param string $karrierepfad_kurzbz
	 * @return boolean
	 */
	public function loadKurzbz($karrierepfad_kurzbz)
	{
		$qry = "SELECT karrierepfad_id FROM public.tbl_karrierepfad
				WHERE karrierepfad_kurzbz=".$this->db_add_param($karrierepfad_kurzbz);

		if (!$this->db_query($qry) || !($row = $this->db_fetch_object()))
		{
			$this->errormsg = 'Karrierepfad wurde nicht gefunden';
			return false;
		}
		return $this->load($row->karrierepfad_id);
	}

	/**
	 * Loads the Karrierepfade linked to a Studienplan into $this->result.
	 * A Karrierepfad is selectable for a prestudent if it is aktiv and linked to the
	 * Studienplan of the last status. A value stored earlier stays valid without this check.
	 * @param integer $studienplan_id
	 * @param boolean $aktiv true: only active Karrierepfade
	 * @return boolean
	 */
	public function getByStudienplan($studienplan_id, $aktiv = true)
	{
		if (!is_numeric($studienplan_id))
		{
			$this->errormsg = 'Studienplan ID ist ungueltig';
			return false;
		}

		$sprache = new sprache();
		$qry = "SELECT kp.*, ".$sprache->getSprachQuery('bezeichnung_mehrsprachig')."
				FROM public.tbl_karrierepfad kp
				JOIN public.tbl_karrierepfad_studienplan USING(karrierepfad_id)
				WHERE studienplan_id=".$this->db_add_param($studienplan_id, FHC_INTEGER);
		if ($aktiv)
			$qry .= " AND kp.aktiv";
		$qry .= " ORDER BY kp.sort NULLS LAST, kp.karrierepfad_kurzbz";

		if (!$this->db_query($qry))
		{
			$this->errormsg = 'Fehler beim Laden der Karrierepfade';
			return false;
		}

		while ($row = $this->db_fetch_object())
		{
			$obj = new karrierepfad();
			$this->setFromRow($obj, $row, $sprache);
			$this->result[] = $obj;
		}
		return true;
	}

	private function setFromRow($obj, $row, $sprache)
	{
		$obj->karrierepfad_id = $row->karrierepfad_id;
		$obj->karrierepfad_kurzbz = $row->karrierepfad_kurzbz;
		$obj->karrierepfad_typ_kurzbz = $row->karrierepfad_typ_kurzbz;
		$obj->studiengang_kz = $row->studiengang_kz;
		$obj->bezeichnung_mehrsprachig = $sprache->parseSprachResult('bezeichnung_mehrsprachig', $row);
		$obj->sort = $row->sort;
		$obj->aktiv = $this->db_parse_bool($row->aktiv);
		$obj->insertamum = $row->insertamum;
		$obj->insertvon = $row->insertvon;
		$obj->updateamum = $row->updateamum;
		$obj->updatevon = $row->updatevon;
	}
}
