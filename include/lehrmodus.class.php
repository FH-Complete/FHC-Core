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

require_once(dirname(__FILE__). '/basis_db.class.php');
require_once(dirname(__FILE__). '/sprache.class.php');
require_once(dirname(__FILE__). '/functions.inc.php');

class lehrmodus extends basis_db
{
	//Objekt Lehrmodus
	public $result = array();

	//Tabellenspalten
	public $lehrmodus_kurzbz;
	public $bezeichnung_mehrsprachig;
	public $aktiv;

	/**
	 * Konstruktor - Laedt optional einen Lehrmodus
	 * @param lehrmodus_kurbz Lehrmodus der geladen werden soll
	 */
	public function __construct($lehrmodus_kurzbz = null)
	{
		parent::__construct();

		if($lehrmodus_kurzbz != null)
			$this->load($lehrmodus_kurzbz);
	}


	/**
	 * Liefert alle Lehrmodi aus der table tbl_lehrmodus
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function getAll()
	{
		$sprache = new sprache();
		$qry = "SELECT *, ".$sprache->getSprachQuery('bezeichnung_mehrsprachig')."
		FROM lehre.tbl_lehrmodus";

		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$lehrmodus = new lehrmodus();

				$lehrmodus->lehrmodus_kurzbz = $row->lehrmodus_kurzbz;
				$lehrmodus->aktiv = $this->db_parse_bool($row->aktiv);
				$lehrmodus->bezeichnung_mehrsprachig = $sprache->parseSprachResult('bezeichnung_mehrsprachig', $row);

				$this->result[] = $lehrmodus;
			}
			return true;
		}
		else
		{
			$this->errormsg = "Fehler bei der Abfrage aufgetreten";
			return false;
		}
	}

	/**
	 * Laedt einen Lehrmodus
	 * @param lehrmodus_kurzbz ID des Datensatzes der zu laden ist
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function load($lehrmodus_kurzbz)
	{
		$sprache = new sprache();
		$qry = "SELECT
					*,".$sprache->getSprachQuery('bezeichnung_mehrsprachig')."
				FROM
					lehre.tbl_lehrmodus
				WHERE
					lehrmodus_kurzbz=".$this->db_add_param($lehrmodus_kurzbz).";";

		if(!$this->db_query($qry))
		{
			$this->errormsg = 'Fehler beim Lesen vom Lehrmodus';
			return false;
		}

		if($row = $this->db_fetch_object())
		{
			$this->lehrmodus_kurzbz = $row->lehrmodus_kurzbz;
			$this->bezeichnung_mehrsprachig = $sprache->parseSprachResult('bezeichnung_mehrsprachig',$row);
			$this->aktiv = $this->db_parse_bool($row->aktiv);
		}
		else
		{
			$this->errormsg = 'Es ist kein Lehrmodus mit dieser ID vorhanden';
			return false;
		}
		return true;
	}

	/**
	 * Baut die Datenstruktur für senden als JSON Objekt auf
	 */
	// public function cleanResult()
	// {
	// 	$data = array();
	// 	if(count($this->result)>0)
	// 	{
	// 		foreach ($this->result as $lm)
	// 		{
	// 			$obj = new stdClass();
	// 			$obj->lehrmodus_kurzbz = $lm->lehrmodus_kurzbz;
	// 			$data[] = $obj;
	// 		}
	// 	}
	// 	return $data;
	// }
}

?>
