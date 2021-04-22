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
require_once(dirname(__FILE__).'/sprache.class.php');

class lehrmodus extends basis_db {

	public $new;
	//Objekt Lehrmodus
	public $lehrmodus = array();

	//Tabellenspalten
	public $lehrmodus_kurzbz;
	public $bezeichnung_mehrsprachig;
	public $aktiv;

	/**
	 * Konstruktor - Laedt optional einen Lehrmodus
	 * @param $lehrmodus_kurbz Lehrmodus der geladen werden soll
	 */
	public function __construct()
	{
		parent::__construct();

		if(!is_null($lehrmodus_kurzbz))
			$this->load($lehrmodus_kurzbz);

	}

	/**
	 * Laedt einen Lehrmodus
	 * @param lehrmodus_kurzbz ID des Datensatzes der zu laden ist
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function load($lehrmodus_kurzbz)
	{
		$sprache = new sprache();
		$qry = "SELECT *,".$sprache->getSprachQuery('bezeichnung_mehrsprachig')." FROM lehre.tbl_lehrmodus WHERE lehrmodus_kurzbz=".$this->db_add_param($lehrmodus_kurzbz).";";

		if(!$this->db_query($qry))
		{
			$this->errormsg = 'Fehler beim Lesen vom Lehrmodus';
			return false;
		}

		if($row = $this->db_fetch_object())
		{
			$this->lehrmodus_kurzbz = $row->lehrmodus_kurzbz;
			$this->bezeichnung_mehrsprachig = $sprache->parseSprachResult('bezeichnung_mehrprachig',$row);
		}
		else
		{
			$this->errormsg = 'Es ist kein Lehrmodus mit dieser ID vorhanden';
			return false;
		}
		return true;
	}

	/**
	* Liefert alle Lehrmodi aus der table tbl_lehrmodus
	* @return true wenn ok, false im Fehlerfall
	*/
	public function getAll(){
		$sprache = new sprache();
		$qry = "SELECT *,".$sprache->getSprachQuery('bezeichnung_mehrsprachig')." FROM lehre.tbl_lehrmodus ORDER BY lehrmodus_kurzbz;";

		if (!$this->db_query($qry)) {
			$this->errormsg = 'Fehler beim Lesen Lehrmodus';
			return false;
		}

		while ($row = $this->db_fetch_object())
		{
			$lm = new lehrmodus();

			$lm->lehrmodus_kurzbz = $row->lehrmodus_kurzbz;
			$lm->bezeichnung_mehrsprachig = $sprache->parseSprachResult('bezeichnung_mehrsprachig',$row);
			$this->lehrmodus[] = $lm;
		}
		return true;
	}

	/**
	 * Speichert den Lehrmodus in die Datenbank
	 * Wenn $new auf true gesetzt ist wird ein neuer Datensatz
	 * angelegt, ansonsten der Datensatz mit $lehrfach_nr upgedated
	 * @return true wenn erfolgreich, false im Fehlerfall
	 */
	// public function save()
	// {
	// 	//Variablen auf Gueltigkeit pruefen
	// 	// if(!$this->validate())
	// 	// 	return false;
	//
	// 	if($this->new)
	// 	{
	// 		$qry = "INSERT INTO lehre.tbl_lehrmodus (lehrmodus_kurzbz, bezeichnung, verplanen)
	// 		        VALUES(".$this->db_add_param($this->lehrform_kurzbz).",".
	// 				$this->db_add_param($this->bezeichnung).','.
	// 				$this->db_add_param($this->verplanen, FHC_BOOLEAN).');';
	// 	}
	// 	else
	// 	{
	// 		$qry = 'UPDATE lehre.tbl_lehrform SET'.
	// 		       ' bezeichnung='.$this->db_add_param($this->bezeichnung).','.
	// 		       ' verplanen='.$this->db_add_param($this->verplanen, FHC_BOOLEAN).
	// 		       " WHERE lehrform_kurzbz=".$this->db_add_param($this->lehrform_kurzbz).';';
	// 	}
	//
	// 	if($this->db_query($qry))
	// 	{
	// 		//Log schreiben
	// 		return true;
	// 	}
	// 	else
	// 	{
	// 		$this->errormsg = 'Fehler beim Speichern der Lehrform:'.$qry;
	// 		return false;
	// 	}
	// }
}

?>
