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

class besqualcode extends basis_db
{
	//Objekt besqualcode
	public $result = array();

	//Tabellenspalten
	public $besqualcode;
	public $besqualbez;

	/**
	 * Konstruktor - Laedt optional einen besqualcode
	 * @param char $besqualcode Besqualcode der geladen werden soll.
	 */
	public function __construct($besqualcode = null)
	{
		parent::__construct();

		if($besqualcode != null)
			$this->load($besqualcode);
	}


	/**
	 * Liefert alle Lehrmodi aus der table tbl_besqualcode
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function getAll()
	{
		$qry = "SELECT * FROM bis.tbl_besqual";

		if ($this->db_query($qry))
		{
			while ($row = $this->db_fetch_object())
			{
				$besqualcode = new besqualcode();

				$besqualcode->besqualcode = $row->besqualcode;
				$besqualcode->besqualbez = $row->besqualbez;

				$this->result[] = $besqualcode;
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
	 * Laedt einen besqualcode
	 * @param char $besqualcode ID des Datensatzes der zu laden ist.
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function load($besqualcode)
	{
		$qry = "SELECT
					*
				FROM
					bis.tbl_besqual
				WHERE
					besqualcode=".$this->db_add_param($besqualcode).";";

		if (!$this->db_query($qry))
		{
			$this->errormsg = 'Fehler beim Lesen vom besqualcode';
			return false;
		}

		if ($row = $this->db_fetch_object())
		{
			$this->besqualcode = $row->besqualcode;
			$this->besqualbez = $row->besqualbez;
		}
		else
		{
			$this->errormsg = 'Es ist kein besqualcode mit dieser ID vorhanden';
			return false;
		}
		return true;
	}
}

?>
