<?php
/* Copyright (C) 2013 fhcomplete.org
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
 * Authors: Manuela Thamer <manuela.thamer@technikum-wien.at>
 */

require_once(dirname(__FILE__) . '/basis_db.class.php');
require_once(dirname(__FILE__) . '/functions.inc.php');
require_once(dirname(__FILE__).'/sprache.class.php');

class lehrmodus extends basis_db {

	public $result = array();
	public $lehrmodus_kurzbz;
	public $bezeichnung_mehrsprachig;

	public function __construct()
	{
		parent::__construct();

	}

	/**
	 * Laedt einen Lehrmodus
	 * @param lehrmodus_kurzbz ID des Datensatzes der zu laden ist
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function load($lehrmodus_kurzbz)
	{
		$qry = "SELECT * FROM lehre.tbl_lehrmodus WHERE lehrmodus_kurzbz = ".$this->db_add_param($lehrmodus_kurzbz).";";

		if(!$this->db_query($qry))
		{
			$this->errormsg = 'Fehler beim Laden des Datensatzes';
			return false;
		}

		if($row = $this->db_fetch_object())
		{
			$this->lehrmodus_kurzbz = $row->lehrmodus_kurzbz;
			$this->bezeichnung_mehrsprachig = $row->bezeichnung_mehrsprachig;
		}
		else
		{
			$this->errormsg = 'Es ist kein Datensatz mit dieser ID vorhanden';
			return false;
		}
		return true;
	}

	/**
	* Laedt alle Lehrmodi aus der table tbl_lehrmodus
	* @return true wenn ok, false im Fehlerfall
	*/
	public function getAll(){
		$qry = "SELECT * FROM lehre.tbl_lehrmodus;";
		if (!$this->db_query($qry)) {
			$this->errormsg = 'Datensatz konnte nicht geladen werden';
			return false;
		}

		while ($row = $this->db_fetch_object()) {
			$lehrmodus = new lehrmodus();
			$lehrmodus->lehrmodus_kurzbz = $row->lehrmodus_kurzbz;
			$lehrmodus->bezeichnung_mehrsprachig = $row->bezeichnung_mehrsprachig;
			$this->result[] = $lehrmodus;
		}
		return true;
	}

	/**
	 * Baut die Datenstruktur für senden als JSON Objekt auf
	 */
	public function cleanResult()
	{
		$data = array();
		if(count($this->result)>0)
		{
			foreach ($this->result as $lt)
			{
				$obj = new stdClass();
				$obj->lehrtyp_kurzbz = $lt->lehrtyp_kurzbz;
				$obj->bezeichnung = $lt->bezeichnung;
				$data[] = $obj;
			}
		}
		return $data;
	}
}

?>
