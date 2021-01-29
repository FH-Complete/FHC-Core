<?php
/* Copyright (C) 2020 FH Technikum-Wien
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
 * Authors: Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>,
 */
/**
 * Klasse zur Verwaltung der Abschlusspruefungsantritte
 */
require_once(dirname(__FILE__).'/basis_db.class.php');

class abschlusspruefung_antritt extends basis_db
{
	public $new;
	public $result = array();

	//Tabellenspalten
	public $pruefungsantritt_kurzbz;
	public $bezeichnung;
	public $bezeichnung_english;
	public $sort;

	/**
	 * Konstruktor
	 * @param $pruefungsantritt_kurzbz kurzbz des zu ladenden Datensatzes
	 */
	public function __construct($pruefungsantritt_kurzbz=null)
	{
		parent::__construct();

		if(!is_null($pruefungsantritt_kurzbz))
			$this->load($pruefungsantritt_kurzbz);
	}

	/**
	 * Laedt einen Datensatz
	 * @param $pruefungsantritt_kurzbz Kurzbz des zu ladenden Datensatzes
	 */
	public function load($pruefungsantritt_kurzbz)
	{
		//laden des Datensatzes
		$qry = "SELECT
					*
				FROM
					lehre.tbl_abschlusspruefung_antritt
				WHERE pruefungsantritt_kurzbz=".$this->db_add_param($pruefungsantritt_kurzbz).";";

		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$this->pruefungsantritt_kurzbz = $row->pruefungsantritt_kurzbz;
				$this->bezeichnung = $row->bezeichnung;
				$this->bezeichnung_english = $row->bezeichnung_english;
				$this->sort = $row->sort;

				return true;
			}
			else
			{
				$this->errormsg = 'Fehler bei der Datenbankabfrage';
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Fehler bei der Datenbankabfrage';
			return false;
		}
	}

	/**
	 * Liefert alle Abschlussprüfungsantritt
	 * @return boolean true wenn erfolgreich, false im Fehlerfall
	 */
	public function getAll()
	{
		$qry = "SELECT
					*
				FROM
					lehre.tbl_abschlusspruefung_antritt
				ORDER BY sort";

		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$obj = new abschlusspruefung_antritt();
				$obj->pruefungsantritt_kurzbz = $row->pruefungsantritt_kurzbz;
				$obj->bezeichnung = $row->bezeichnung;
				$obj->bezeichnung_english = $row->bezeichnung_english;
				$obj->sort = $row->sort;
				$this->result[] = $obj;
			}
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Daten';
			return false;
		}
	}
}
?>
