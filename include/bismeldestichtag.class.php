<?php
/* Copyright (C) 2007 Technikum-Wien
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
 * Authors: Alexei Karpenko <karpenko@technikum-wien.at>,
 */
require_once(dirname(__FILE__).'/basis_db.class.php');

class bismeldestichtag extends basis_db
{
	//Tabellenspalten
	public $meldestichtag_id;		// integer
	public $meldestichtag;			// date
	public $studiensemester_kurzbz;	// varchar(16)
	public $insertamum;				// timestamp
	public $insertvon;				// varchar(16)
	public $updateamum;				// timestamp
	public $updatevon;				// varchar(16)

	// ErgebnisArray
	public $result=array();

	/**
	 * Konstruktor - Uebergibt die Connection und laedt optional einen Meldestichtag
	 * @param $meldestichtag_id Stichtag der geladen werden soll (default=null)
	 */
	public function __construct($meldestichtag_id=null)
	{
		parent::__construct();

		if(!is_null($meldestichtag_id))
			$this->load($meldestichtag_id);
	}

	/**
	 * Laedt Meldestichtag mit der uebergebenen ID
	 * @param $meldestichtag_id ID des Stichtags der geladen werden soll
	 */
	public function load($meldestichtag_id)
	{
		$qry = "SELECT * FROM bis.tbl_meldestichtag WHERE meldestichtag_id=".$this->db_add_param($meldestichtag_id, FHC_INTEGER);

		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$this->meldestichtag_id = $row->meldestichtag_id;
				$this->meldestichtag = $row->meldestichtag;
				$this->studiensemester_kurzbz = $row->studiensemester_kurzbz;
				$this->insertamum = $row->insertamum;
				$this->insertvon = $row->insertvon;
				$this->updateamum = $row->updateamum;
				$this->updatevon = $row->updatevon;
				return true;
			}
			else
			{
				$this->errormsg = 'Kein Eintrag gefunden';
				return false;
			}
		}
		else
		{
			$this->errormsg = "Fehler beim Laden des Stichtags";
			return false;
		}
	}

	/**
	 * Prueft die Variablen vor dem Speichern
	 * auf Gueltigkeit.
	 * @return true wenn ok, false im Fehlerfall
	 */
	protected function validate()
	{
		return true;
	}

	/**
	 * Liefert die Antwort eines Pruefling zu einer Frage
	 *
	 * @param $pruefling_id
	 * @param $frage_id
	 * @return boolean
	 */
	public function getLastMeldestichtag($studiensemester_kurzbz = null)
	{
		$qry = "SELECT
					meldestichtag, studiensemester_kurzbz
				FROM
					bis.tbl_meldestichtag";

		if (isset($studiensemester_kurzbz))
		{
			$qry .=	"
				WHERE
					studiensemester_kurzbz=".$this->db_add_param($studiensemester_kurzbz);
		}

		$qry .= "
				ORDER BY
					meldestichtag DESC
				LIMIT 1;"

		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$obj = new antwort();

				$obj->meldestichtag_id = $row->meldestichtag_id;
				$obj->meldestichtag = $row->meldestichtag;
				$obj->studiensemester_kurzbz = $row->studiensemester_kurzbz;
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;
				$obj->updateamum = $row->updateamum;
				$obj->updatevon = $row->updatevon;

				$this->result[] = $obj;
			}

			return true;
		}
		else
		{
			$this->errormsg = 'Meldestichtag konnte nicht geladen werden';
			return false;
		}
	}
}
?>
